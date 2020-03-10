<?php

namespace App\Http\Controllers\API\CL;

use App\Achievement\Exceptions\CourseIsNotFinishedException;
use App\Achievement\Strategies\FinishCourseAchievementStrategy;
use App\Achievement\Strategies\ViewLessonAchievementStrategy;
use App\Entities\Achievement;
use App\Models\CL\Lesson;
use App\Models\CL\UserCourse;
use App\Models\CL\UserLesson;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/cl/lesson/{lesson_id}",
     *     summary="Get Lesson.",
     *     tags={"CL"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="data",
     *                      @SWG\Items(ref="#/definitions/Lesson")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function show(Lesson $lesson)
    {
        if (!$lesson->user_access) {
            return response()->json(['success' => false, 'data' => ['message' => 'Access for this lesson is denied.']], 403);
        }

        $lesson->append('comments_list');
        $lesson->append('files_list');

        /** @var User $user */
        if ($user = Auth::guard('api')->user()) {
            $context = new Achievement(new ViewLessonAchievementStrategy($user));

            try {
                $context->run();
            } catch (\Exception $e) {
                \Log::info($e);
            }
        }

        return response()->json(['success' => true, 'data' => $lesson], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/lesson/start/{lesson_id}",
     *     summary="Start Lesson for User.",
     *     tags={"CL"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function start(Lesson $lesson)
    {
        if (!Auth::guard('api')->guest() && !Auth::guard('api')->user()->lessons->contains($lesson->id)) {
            UserLesson::create([
                'user_id' => Auth::guard('api')->id(),
                'lesson_id' => $lesson->id,
            ]);
        } else {
            return response()->json(['success' => false, 'data' => ['message' => 'User not authorized.']], 200);
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Lesson started.']], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/lesson/finish/{lesson_id}",
     *     summary="Fininsh Lesson for User.",
     *     tags={"CL"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *         ),
     *     ),
     * )
     */
    /**
     * @param Lesson $lesson
     * @return \Illuminate\Http\JsonResponse
     */
    public function finish(Lesson $lesson)
    {
        $user = Auth::guard('api')->user();
        if (is_null($user)) {
            return response()->json(['success' => false, 'data' => ['message' => 'User not authorized.']], 200);
        }

        $userLesson = UserLesson::where('user_id', $user->id)->where('lesson_id', $lesson->id)->first();
        $userCourse = UserCourse::where('user_id', $user->id)->where('course_id', $lesson->course_id)->first();

        if (is_null($userLesson)) {
            UserLesson::create([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'finished' => 1,
            ]);
        } else {
            $userLesson->update(['finished' => 1]);
        }

        if (is_null($userCourse)) {
            UserCourse::create([
                'user_id' => $user->id,
                'course_id' => $lesson->course_id,
            ]);
        }

        $courseProgress = $user->lessons()->where('course_id', $lesson->course_id)->where('finished', 1)->count()/$lesson->course->lessons()->count();
        $userCourse->update([
            'progress' => round($courseProgress * 100, 2),
            'finished' => floor($courseProgress),
            'finished_at' => floor($courseProgress) == 1 ? date("Y-m-d H:i:s", time()) : null,
        ]);
        /** @var Achievement $context */

        $context = new Achievement(new FinishCourseAchievementStrategy($user, $courseProgress));

        try {
            $context->run();
        } catch (CourseIsNotFinishedException $e) {
            \Log::info($e);
        } catch (\Exception $e) {
            \Log::info($e);
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Lesson finished.']], 200);
    }
}
