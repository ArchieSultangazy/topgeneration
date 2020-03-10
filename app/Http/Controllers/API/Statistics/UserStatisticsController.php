<?php

namespace App\Http\Controllers\API\Statistics;

use App\Models\CL\Course;
use App\Models\CL\UserCourse;
use App\Models\KB\Article;
use App\Models\UserAchievement;
use App\Models\UserLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Analytics;
use Spatie\Analytics\Period;

class UserStatisticsController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/statistics/users",
     *     summary="Statistics of users.",
     *     tags={"Statistics"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="statistics", type="object",
     *                      @SWG\Property(property="users_total", type="integer"),
     *                      @SWG\Property(property="active_users_total", type="integer"),
     *                      @SWG\Property(property="active_users_total_percent", type="integer"),
     *                      @SWG\Property(property="online_users_total", type="integer"),
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function user() {
        $stats = [
            'users_total' => User::query()->count(),
            'active_users_total' => DB::table('oauth_access_tokens')
                ->select('user_id', DB::raw('COUNT( DISTINCT DATE( created_at )) AS days'))
                ->where('created_at', '>', DB::raw('DATE_SUB( CURRENT_TIMESTAMP, INTERVAL 3 DAY )'))
                ->groupBy('user_id')
                ->havingRaw('days >= 3')
                ->get()
                ->count(),
        ];
        $stats['active_users_total_percent'] = intval($stats['active_users_total']/$stats['users_total']);
        $stats['online_users_total'] = 0;

        $redis = Redis::connection();
        foreach (User::query()->pluck('id') as $item) {
            if (!is_null($redis->get('last_seen_' . $item)) && time() - strtotime($redis->get('last_seen_' . $item)) < 90)
                $stats['online_users_total']++;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/statistics/users/contents",
     *     summary="Statistics of contents.",
     *     tags={"Statistics"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *     @SWG\Parameter(name="date_from", in="formData", description="Filter by date from (Ex: 16-07-2019)", required=false, type="string"),
     *     @SWG\Parameter(name="date_to", in="formData", description="Filter by date to (Ex: 16-07-2019)", required=false, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="statistics", type="object",
     *                      @SWG\Property(property="course_process_avg", type="integer"),
     *                      @SWG\Property(property="finished_courses_percent", type="integer"),
     *                      @SWG\Property(property="avg_days_to_finish_course", type="integer"),
     *                      @SWG\Property(property="avg_rating_courses_percent", type="integer"),
     *                      @SWG\Property(property="articles_views", type="integer"),
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function content(Request $request) {
        $startDate = Carbon::createFromFormat('d-m-Y H:i:s', '01-05-2019 00:00:00');
        $endDate = Carbon::now();

        if ($request->get('date_from') && $request->get('date_to')) {
            try {
                $startDate = Carbon::createFromFormat('d-m-Y H:i:s', $request->get('date_from') . '00:00:00');
                $endDate = Carbon::createFromFormat('d-m-Y H:i:s', $request->get('date_to') . '00:00:00');
            } catch (\Exception $exception) {}
        }

        $avgCoursesProcess = UserCourse::query()
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->avg('progress');

        $finishedCoursesCount = UserCourse::query()
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->where('finished', 1)->count();
        $allCoursesCount = UserCourse::query()
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->count();
        $avgDays = UserCourse::query()->select('finished', DB::raw('DATEDIFF(updated_at, created_at) as days'))
            ->where('finished', 1)
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->get()
            ->avg('days');
        $avgRating = Course::query()
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->avg('rating');
        $articleViews = Article::query()
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->sum('views');

        $stats = [
            'course_process_avg' => $avgCoursesProcess,
            'finished_courses_percent' => $allCoursesCount == 0 ? 0 : intval(100 * $finishedCoursesCount/$allCoursesCount),
            'avg_days_to_finish_course' => $avgDays,
            'avg_rating_courses_percent' => intval($avgRating * 20),
            'articles_views' => intval($articleViews),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/statistics/users/auditory",
     *     summary="Statistics of auditory.",
     *     tags={"Statistics"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *     @SWG\Parameter(name="date_from", in="formData", description="Filter by date from (Ex: 16-07-2019)", required=false, type="string"),
     *     @SWG\Parameter(name="date_to", in="formData", description="Filter by date to (Ex: 16-07-2019)", required=false, type="string"),
     *     @SWG\Parameter(name="max_user", in="formData", description="Max amount of user Ids", required=false, type="integer"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="statistics", type="object",
     *                      @SWG\Property(property="register_conversion", type="integer"),
     *                      @SWG\Property(property="retention", type="integer"),
     *                      @SWG\Property(property="all_users", type="integer"),
     *                      @SWG\Property(property="pages_count", type="integer"),
     *                      @SWG\Property(property="not_registered_users", type="integer"),
     *                      @SWG\Property(property="registered_users", type="integer"),
     *                      @SWG\Property(property="rejects_percent", type="integer"),
     *                      @SWG\Property(property="registers_count", type="integer"),
     *                      @SWG\Property(property="user_activities_by_date", type="array",
     *                          @SWG\Items(
     *                              @SWG\Property(property="date", type="integer"),
     *                          ),
     *                      ),
     *                      @SWG\Property(property="user_registrations_by_date", type="array",
     *                          @SWG\Items(
     *                              @SWG\Property(property="date", type="integer"),
     *                          ),
     *                      ),
     *                  ),
     *                  @SWG\Property(property="active_users_ids", type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="user_ids", type="integer"),
     *                      ),
     *                  ),
     *                  @SWG\Property(property="new_users_ids", type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="user_ids", type="integer"),
     *                      ),
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function auditory(Request $request) {
        $startDate = Carbon::createFromFormat('d-m-Y H:i:s', '01-05-2019 00:00:00');
        $endDate = Carbon::now();

        if ($request->get('date_from') && $request->get('date_to')) {
            try {
                $startDate = Carbon::createFromFormat('d-m-Y H:i:s', $request->get('date_from') . '00:00:00');
                $endDate = Carbon::createFromFormat('d-m-Y H:i:s', $request->get('date_to') . '00:00:00');
            } catch (\Exception $exception) {}
        }

        $usersCount = Analytics::performQuery(
            Period::create($startDate, $endDate),
            'ga:sessionCount',
            [
                'metrics' => 'ga:sessions',
            ]
        )["rows"][0][0];

        $registeredUsers = UserLog::where('action', 'register')
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->get()
            ->count();

        $authorizedUsers = UserLog::where('section', 'auth')
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->groupBy('user_id')
            ->get()
            ->count();

        $returnedUsersCount = Analytics::performQuery(
            Period::create($startDate, $endDate),
            'ga:sessionCount',
            [
                'metrics' => 'ga:sessions',
                'dimensions' => 'ga:userType',
            ]
        )["rows"][1][1];

        $pagesCount = Analytics::performQuery(
            Period::create($startDate, $endDate),
            'ga:pageviews'
        )["rows"][0][0];

        $bouncesRate = Analytics::performQuery(
            Period::create($startDate, $endDate),
            'ga:bounceRate'
        )["rows"][0][0];

        $user_activities = UserAchievement::query()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as actions'))
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->groupBy('date')
            ->pluck('actions', 'date')
            ->toArray();

        $user_registrations = User::query()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as registers'))
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->groupBy('date')
            ->pluck('registers', 'date')
            ->toArray();

        $stats = [
            'register_conversion' => number_format(100 * $registeredUsers / floatval($usersCount), 2),
            'retention' => number_format(100 * floatval($returnedUsersCount) / floatval($usersCount), 2),
            'all_users' => intval($usersCount),
            'pages_count' => intval($pagesCount),
            'not_registered_users' => intval($usersCount) - $authorizedUsers,
            'registered_users' => $authorizedUsers,
            'rejects_percent' => number_format(floatval($bouncesRate), 2),
            'registers_count' => $registeredUsers,
            'user_activities_by_date' => $user_activities,
            'user_registrations_by_date' => $user_registrations,
        ];

        $active_users = DB::table('oauth_access_tokens')
            ->select('user_id', DB::raw('COUNT( DISTINCT DATE( created_at )) AS days'))
            ->where('created_at', '>', DB::raw('DATE_SUB( CURRENT_TIMESTAMP, INTERVAL 20 DAY )'))
            ->groupBy('user_id')
            ->havingRaw('days >= 0')
            ->take(intval($request->get('max_user') ?? 10))
            ->pluck('user_id')
            ->toArray();

        $new_users = User::query()->orderBy('created_at', 'DESC')
            ->take(intval($request->get('max_user') ?? 10))
            ->pluck('id')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'active_users_ids' => $active_users,
                'new_users_ids' => $new_users,
            ]], 200);
    }
}
