<?php

namespace App\Http\Controllers\API\Statistics;

use App\Models\CL\Rating\RateLesson;
use App\Models\CL\Test;
use App\Models\CL\UserCourse;
use App\Models\CL\UserLesson;
use App\Models\UserAchievement;
use App\Models\UserResults;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class InfoStatisticsController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/statistics/users/index",
     *     summary="Statistics list of users.",
     *     tags={"Statistics"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *      @SWG\Parameter(name="search", description="Search word", in="formData", type="string",),
     *      @SWG\Parameter(name="per_page", description="Items per page (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="page", description="Page number (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="order_by[column]", description="Custom orderBy (Ex: ?order_by[rating]=DESC&order_by[views]=ASC)", in="formData", type="string",),
     *
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/User")
     *                      ),
     *                      @SWG\Property(property="first_page_url", type="string"),
     *                      @SWG\Property(property="from", type="integer"),
     *                      @SWG\Property(property="last_page", type="integer"),
     *                      @SWG\Property(property="last_page_url", type="string"),
     *                      @SWG\Property(property="next_page_url", type="string"),
     *                      @SWG\Property(property="path", type="string"),
     *                      @SWG\Property(property="per_page", type="integer"),
     *                      @SWG\Property(property="prev_page_url", type="string"),
     *                      @SWG\Property(property="to", type="integer"),
     *                      @SWG\Property(property="total", type="integer"),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function index(Request $request) {
        $users = User::query();
        $keyword = $request->get('search');
        $orderBy = $request->get('order_by');

        if (!is_null($keyword)) {
            $users = $users->where('firstname', 'LIKE', "%$keyword%")
                ->orWhere('lastname', 'LIKE', "%$keyword%")
                ->orWhere('middlename', 'LIKE', "%$keyword%");
        }
        if (!is_null($orderBy)) {
            foreach ($orderBy as $column => $method) {
                $users = $users->orderBy($column, $method);
            }
        }

        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $users = $users->paginate($per_page);

        $users->map(function ($user) {
            $user->append('registration_date');
            $user->append('achievement_points');
            $user->makeHidden('achievements');
            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/statistics/users/info/{user_id}",
     *     summary="Info of User by ID.",
     *     tags={"Statistics"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data",
     *                  @SWG\Items(ref="#/definitions/User")
     *              ),
     *         ),
     *     ),
     * )
     */
    public function userInfo(User $user) {
        $user->append('registration_date');
        $user->append('achievement_points');
        $user->makeHidden('achievements');
        $user->append('region');

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/statistics/users/statistics/{user_id}",
     *     summary="Statistics of User by ID.",
     *     tags={"Statistics"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="user",
     *                      @SWG\Items(ref="#/definitions/User")
     *                  ),
     *                  @SWG\Property(property="external_statistics", type="object",
     *                      @SWG\Property(property="lessons_rate_avg", type="integer"),
     *                      @SWG\Property(property="started_courses_count", type="integer"),
     *                      @SWG\Property(property="finished_courses_count", type="integer"),
     *                      @SWG\Property(property="articles_views_count", type="integer"),
     *
     *                      @SWG\Property(property="user_actions", type="object",
     *                          @SWG\Property(property="actions_by_dates", type="integer"),
     *                      ),
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function userStatistics(User $user) {
        $redis = Redis::connection();
        $redis_key = 'user_articles_' . $user->id;

        $user->append('achievement_points');
        $user->makeHidden('achievements');
        $user->append('progress_in_system');

        $stats['lessons_rate_avg'] = number_format(
            floatval(RateLesson::query()->where('user_id', $user->id)->avg('value') * 20),
            2
        );
        $stats['started_courses_count'] = UserCourse::query()->where('user_id', $user->id)->where('finished', 0)->count();
        $stats['finished_courses_count'] = UserCourse::query()->where('user_id', $user->id)->where('finished', 1)->count();
        $stats['articles_views_count'] = count(json_decode($redis->get($redis_key)) ?? []);
        $stats['user_actions'] = UserAchievement::query()
                ->select('user_id', DB::raw('DATE(created_at) as date'), DB::raw('count(*) as actions'))
                ->where('user_id', $user->id)
                ->groupBy('date')
                ->pluck('actions', 'date')
                ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'external_statistics' => $stats,
            ]
        ], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/statistics/users/courses/{user_id}",
     *     summary="Statistics of User's courses.",
     *     tags={"Statistics"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                      @SWG\Property(property="course_name", type="string",),
     *                      @SWG\Property(property="course_themes", type="object",
     *                          @SWG\Property(property="name", type="string"),
     *                      ),
     *                      @SWG\Property(property="started_date", type="string"),
     *                      @SWG\Property(property="finished_date", type="string"),
     *                      @SWG\Property(property="last_activity_date", type="string"),
     *                      @SWG\Property(property="avg_lessons_rate", type="integer"),
     *                      @SWG\Property(property="count_lessons_rate", type="integer"),
     *                      @SWG\Property(property="tests_result", type="integer"),
     *                      @SWG\Property(property="total_tests_count", type="integer"),
     *                      @SWG\Property(property="passed_lessons_count", type="integer"),
     *                      @SWG\Property(property="total_lessons_count", type="integer"),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function userCourses(User $user) {
        $userCourses = UserCourse::query()->where('user_id', $user->id)->get();

        $stats = [];
        foreach ($userCourses as $course) {
            $lessonsRate = RateLesson::query()->where('user_id', $user->id)
                ->whereIn('lesson_id', $course->course->lessons()->pluck('id')->toArray());
            $testsResult = UserResults::query()->where('user_id', $user->id)
                ->whereIn('lesson_id', $course->course->lessons()->pluck('id')->toArray());
            $lessonsPast = UserLesson::query()->where('user_id', $user->id)
                ->whereIn('lesson_id', $course->course->lessons()->pluck('id')->toArray())
                ->count();

            $stats[$course->id]["course_name"] = $course->course->title;
            $stats[$course->id]["course_themes"] = $course->course->themes()->select('name')->get()->toArray();
            $stats[$course->id]["started_date"] = $course->created_at;
            $stats[$course->id]["finished_date"] = $course->finished_at;
            $stats[$course->id]["last_activity_date"] = $course->updated_at;
            $stats[$course->id]["avg_lessons_rate"] = round(floatval($lessonsRate->avg('value')) * 20, 2);
            $stats[$course->id]["count_lessons_rate"] = $lessonsRate->count();
            $stats[$course->id]["tests_result"] = round($testsResult->avg('result_percent'), 2);
            $stats[$course->id]["total_tests_count"] = Test::query()
                ->whereIn('lesson_id', $course->course->lessons()->pluck('id')->toArray())
                ->count();
            $stats[$course->id]["passed_lessons_count"] = $lessonsPast;
            $stats[$course->id]["total_lessons_count"] = $course->course->lessons()->count();
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }
}
