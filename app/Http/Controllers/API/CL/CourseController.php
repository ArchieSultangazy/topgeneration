<?php

namespace App\Http\Controllers\API\CL;

use App\Models\CL\Course;
use App\Models\CL\Theme;
use App\Models\CL\UserCourse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/cl/course",
     *     summary="Get all courses.",
     *     tags={"CL"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Parameter(name="per_page", description="Items per page (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="page", description="Page number (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="not_published", description="To display not published courses (not_published=1)", in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="articles", type="object",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Course")
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
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $courses = Course::with('authors');

        if ($request->get('not_published') == false) {
            $courses->where('is_published', Course::STATUS_PUBLISHED)
                ->orWhere('is_published', Course::STATUS_DEVELOPING);
        }

        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $courses = $courses->paginate($per_page);

        $courses->map(function ($course) {
            $course->append('themes');
            $course->append('users_finished_amount');
            $course->append('users_finished_percent');
            $course->append('user_course');
            $course->append('last_user_lesson');

            return $course;
        });

        return response()->json(['success' => true, 'data' => ['courses' => $courses]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/course/{course_id}",
     *     summary="Get Course.",
     *     tags={"CL"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="data",
     *                      @SWG\Items(ref="#/definitions/Course")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function show(Course $course)
    {
        $course->append('users_finished_amount');
        $course->append('users_started_ids');
        $course->append('user_course');
        $course->append('authors_list');
        $course->append('lessons_list');
        $course->append('comments_list');
        $course->append('themes');

        return response()->json(['success' => true, 'data' => $course], 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/user/course/start/{course_id}",
     *     summary="Start Course for User.",
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
    public function start(Course $course)
    {
        if (!Auth::guard('api')->guest() && !Auth::guard('api')->user()->courses->contains($course->id)) {
            UserCourse::create([
                'user_id' => Auth::guard('api')->id(),
                'course_id' => $course->id,
            ]);
        } else {
            return response()->json(['success' => false, 'data' => ['message' => 'User not authorized.']], 200);
        }

        return response()->json(['success' => true, 'data' => ['message' => 'Course started.']], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/course/themes",
     *     summary="Get Themes.",
     *     tags={"CL"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="themes",
     *                      @SWG\Items(ref="#/definitions/CLTheme")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getThemes()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'themes' => Theme::all(),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/course/statuses",
     *     summary="Get Statuses. (Keys are as Integer)",
     *     tags={"CL"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="statuses",
     *                      @SWG\Property(property="key", type="string"),
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getStatuses()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'statuses' => Course::getAvailableStatuses(),
            ]], 200);
    }
}
