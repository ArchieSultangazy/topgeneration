<?php

namespace App\Http\Controllers\API\Statistics;

use App\Models\CL\Course;
use App\Models\CL\Lesson;
use App\Models\CL\Rating\RateLesson;
use App\Models\CL\UserCourse;
use App\Models\CL\UserLesson;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CourseStatisticsController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/statistics/cl/lesson/{lesson_id}",
     *     summary="Statistics Lesson.",
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
     *                      @SWG\Property(property="lesson_id", type="integer",),
     *                      @SWG\Property(property="course_id", type="integer"),
     *                      @SWG\Property(property="lesson_title", type="string"),
     *                      @SWG\Property(property="comments_count", type="integer"),
     *                      @SWG\Property(property="finished_users_count", type="integer"),
     *                      @SWG\Property(property="finished_users_percent", type="integer"),
     *                      @SWG\Property(property="views_per_user", type="integer"),
     *                      @SWG\Property(property="reactions", type="object",
     *                          @SWG\Property(property="id", type="integer"),
     *                          @SWG\Property(property="user_id", type="integer"),
     *                          @SWG\Property(property="lesson_id", type="integer"),
     *                          @SWG\Property(property="value", type="integer"),
     *                          @SWG\Property(property="created_at", type="string"),
     *                          @SWG\Property(property="updated_at", type="string"),
     *                      ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function lesson(Lesson $lesson, Request $request) {
        $startDate = Carbon::createFromFormat('d-m-Y H:i:s', '01-05-2019 00:00:00');
        $endDate = Carbon::now();

        if ($request->get('date_from') && $request->get('date_to')) {
            try {
                $startDate = Carbon::createFromFormat('d-m-Y H:i:s', $request->get('date_from') . '00:00:00');
                $endDate = Carbon::createFromFormat('d-m-Y H:i:s', $request->get('date_to') . '00:00:00');
            } catch (\Exception $exception) {}
        }

        $userLesson = UserLesson::query()->where('lesson_id', $lesson->id)
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"]);

        $total_count = $userLesson->count();
        $finished_count = $userLesson->where('finished', 1)->count();
        $reactions = RateLesson::query()
            ->where('lesson_id', $lesson->id)
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->get();

        $stats = [
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->course_id,
            'lesson_title' => $lesson->title,
            'comments_count' => $lesson->comments()->count(),
            'finished_users_count' => $finished_count,
            'finished_users_percent' => $total_count != 0 ? round(100 * $finished_count/$total_count, 2) : 0,
            'views_per_user' => null,
            'reactions' => $reactions,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/statistics/cl/course/{course_id}",
     *     summary="Statistics Course.",
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
     *                      @SWG\Property(property="course_id", type="integer",),
     *                      @SWG\Property(property="course_title", type="string"),
     *                      @SWG\Property(property="lessons_count", type="integer"),
     *                      @SWG\Property(property="comments_count", type="integer"),
     *                      @SWG\Property(property="finished_users_percent", type="integer"),
     *                      @SWG\Property(property="avg_rating", type="integer"),
     *                      @SWG\Property(property="finished_users_count", type="integer"),
     *                      @SWG\Property(property="started_users_count", type="integer"),
     *                      @SWG\Property(property="avg_time_finish", type="string"),
     *                      @SWG\Property(property="avg_progress", type="integer"),
     *                      @SWG\Property(property="lessons", type="object",
     *                          @SWG\Property(property="id", type="integer"),
     *                          @SWG\Property(property="position", type="integer"),
     *                          @SWG\Property(property="title", type="string"),
     *                          @SWG\Property(property="course_id", type="integer"),
     *                          @SWG\Property(property="finished_users_count", type="integer"),
     *                          @SWG\Property(property="finished_users_percent", type="integer"),
     *                          @SWG\Property(property="rating_percent", type="integer"),
     *                      ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function course(Course $course, Request $request) {
        $startDate = Carbon::createFromFormat('d-m-Y H:i:s', '01-05-2019 00:00:00');
        $endDate = Carbon::now();

        if ($request->get('date_from') && $request->get('date_to')) {
            try {
                $startDate = Carbon::createFromFormat('d-m-Y H:i:s', $request->get('date_from') . '00:00:00');
                $endDate = Carbon::createFromFormat('d-m-Y H:i:s', $request->get('date_to') . '00:00:00');
            } catch (\Exception $exception) {}
        }

        $userCourse = UserCourse::query()->where('course_id', $course->id)
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"]);

        $finished_count = $userCourse->where('finished', 1)->count();
        $total_count = $userCourse->count();
        $avg_progress = $userCourse->avg('progress');
        $lessonsIds = $course->lessons()->pluck('id')->toArray();
        $avg_rating = RateLesson::query()
            ->whereIn('lesson_id', $lessonsIds)
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->avg('value');
        $avg_time = intval($userCourse->where('finished', 1)
            ->select('finished', 'finished_at', 'created_at',
                DB::raw('TIMESTAMPDIFF(SECOND, created_at, finished_at) as time'))
            ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
            ->get()
            ->avg('time'));

        $dt1 = new \DateTime("@0");
        $dt2 = new \DateTime("@$avg_time");

        $lessons = DB::table('cl_lessons')
            ->select('cl_lessons.id', 'cl_lessons.position', 'cl_lessons.title', 'cl_lessons.course_id')
            ->where('cl_lessons.course_id', $course->id)
            ->get();

        foreach ($lessons as $lesson) {
            $userLesson = UserLesson::query()->where('lesson_id', $lesson->id)
                ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"]);

            $total_lesson_count = $userLesson->count();
            $finished_lesson_count = $userLesson->where('finished', 1)->count();
            $avg_lesson_rating = RateLesson::query()
                ->where('lesson_id', $lesson->id)
                ->whereBetween('created_at', [$startDate->format('Y-m-d')." 00:00:00", $endDate->format('Y-m-d')." 23:59:59"])
                ->avg('value');

            $lesson->finished_users_count = $finished_lesson_count;
            $lesson->finished_users_percent = $total_lesson_count != 0 ? round(100 * $finished_lesson_count/$total_lesson_count, 2) : 0;
            $lesson->rating_percent = floatval($avg_lesson_rating) * 20;
        }


        $stats = [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'lessons_count' => $course->lessons()->count(),
            'comments_count' => $course->comments()->count(),

            'finished_users_percent' => $total_count != 0 ? round(100 * $finished_count/$total_count, 2) : 0,
            'avg_rating' => round(20 * $avg_rating, 2),
            'finished_users_count' => $finished_count,
            'started_users_count' => $total_count - $finished_count,
            'avg_time_finish' => $dt1->diff($dt2)->format('%y-%m-%d %h:%i:%s'),
            'avg_progress' => $avg_progress,
            'lessons' => $lessons,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }
}
