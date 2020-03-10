<?php

namespace App\Http\Controllers\API\CL\Moderation;

use App\Http\Requests\CL\CourseRequest;
use App\Models\CL\Course;
use App\Models\CL\Rating\RateCourse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * @SWG\Post(
     *     path="/api/cl-moderation/course",
     *     summary="Create new course.",
     *     tags={"CL (Course)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="is_published", description="Status (0 - Is NOT published, 1 - Is published, 2 - Developing)", required=false, in="formData", type="integer",),
     *     @SWG\Parameter(name="locale", description="Locale [only: kk, ru, en]", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="title", description="Title of Article", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="themes[]", description="Themes by ID (Ex: 'themes[0] : 1')", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="authors[]", description="Authors by ID (Ex: 'authors[0] : 1')", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="body_in", description="Description of course", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body_out", description="Description of course", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="img_preview", description="Image file", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="video", description="Video file", required=false, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="course",
     *                      @SWG\Items(ref="#/definitions/Course")
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function store(CourseRequest $request)
    {
        $data = $request->all();
        $course = Course::create($data);

        if (isset($data['themes'])) {
            foreach ($data['themes'] as $themeId) {
                $course->themes()->attach($themeId);
            }
        }

        if (isset($data['authors'])) {
            foreach ($data['authors'] as $authorId) {
                $course->authors()->attach($authorId);
            }
        }

        if ($request->hasFile('img_preview')) {
            $data['img_preview'] = Storage::disk('cl_course')->putFileAs(
                $course->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        if ($request->hasFile('video')) {
            $data['video'] = Storage::disk('cl_course')->putFileAs(
                $course->id, $data['video'],
                'video_' . time() . '.' . $data['video']->getClientOriginalExtension()
            );

            $getID3 = new \getID3;
            $file = $getID3->analyze($request->file('video'));
            $data['duration'] = $file['playtime_seconds'];
        }

        $course->update($data);

        return response()->json(['success' => true, 'data' => ['course' => $course]], 200);
    }

    /**
     * @SWG\Put(
     *     path="/api/cl-moderation/course/{course_id}",
     *     summary="Update course.",
     *     tags={"CL (Course)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="is_published", description="Status (0 - Is NOT published, 1 - Is published, 2 - Developing)", required=false, in="formData", type="integer",),
     *     @SWG\Parameter(name="locale", description="Locale [only: kk, ru, en]", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="title", description="Title of Article", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="themes[]", description="Themes by ID (Ex: 'themes[0] : 1')", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="authors[]", description="Authors by ID (Ex: 'authors[0] : 1')", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="body_in", description="Description of course", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body_out", description="Description of course", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="img_preview", description="Image file", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="video", description="Video file", required=false, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="course",
     *                      @SWG\Items(ref="#/definitions/Course")
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function update(Course $course, CourseRequest $request)
    {
        $data = $request->all();

        $course->themes()->detach();
        if (isset($data['themes'])) {
            foreach ($data['themes'] as $themeId) {
                $course->themes()->attach($themeId);
            }
        }

        $course->authors()->detach();
        if (isset($data['authors'])) {
            foreach ($data['authors'] as $authorId) {
                $course->authors()->attach($authorId);
            }
        }

        if ($request->hasFile('img_preview')) {
            Storage::disk('cl_course')->delete($course->img_preview);
            $data['img_preview'] = Storage::disk('cl_course')->putFileAs(
                $course->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        if ($request->hasFile('video')) {
            Storage::disk('cl_course')->delete($course->video);
            $data['video'] = Storage::disk('cl_course')->putFileAs(
                $course->id, $data['video'],
                'video_' . time() . '.' . $data['video']->getClientOriginalExtension()
            );

            $getID3 = new \getID3;
            $file = $getID3->analyze($request->file('video'));
            $data['duration'] = $file['playtime_seconds'];
        }

        $course->update($data);

        return response()->json(['success' => true, 'data' => ['course' => $course]], 200);
    }

    /**
     * @SWG\Delete(
     *     path="/api/cl-moderation/course/{course_id}",
     *     summary="Delete course.",
     *     tags={"CL (Course)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function destroy(Course $course)
    {
        $response = ['success' => true, 'data' => ['message' => 'Course and its lessons deleted successfully.']];

        $lessons = $course->lessons()->get();
        foreach ($lessons as $lesson) {
            //Storage::disk('cl_lesson')->deleteDirectory($lesson->id);
            $lesson->delete();
        }

        try {
            //Storage::disk('cl_course')->deleteDirectory($course->id);
            $course->delete();
        } catch (\Exception $exception) {
            $response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
        }

        return response()->json($response, 200);
    }

    /**
     * @SWG\Delete(
     *     path="/api/cl-moderation/course/video/{course_id}",
     *     summary="Delete course's video.",
     *     tags={"CL (Course)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="403", description="The action is forbidden.",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function deleteVideo(Course $course)
    {
        $response = ['success' => true, 'data' => ['message' => 'Course\'s video deleted successfully.']];

        try {
            Storage::disk('cl_course')->delete($course->video);
            $course->update(['video' => null]);
        } catch (\Exception $exception) {
            $response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
        }

        return response()->json($response, 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/cl-moderation/course/{course_id}/rate",
     *     summary="Rate course.",
     *     tags={"CL (Course)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="rate_value", description="Rate value (between: 1 and 5)", required=true, in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="total", type="integer"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response="422", description="Validation failed",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="object",),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function rate(Course $course, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate_value' => 'required|numeric|between:1,5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $rateCourse = RateCourse::where('user_id', Auth::id())->where('course_id', $course->id)->first();
        if (is_null($rateCourse)) {
            RateCourse::create([
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'value' => intval($request->get('rate_value')),
            ]);
        } else {
            $rateCourse->update([
                'value' => intval($request->get('rate_value')),
            ]);
        }

        $courseRating = $course->ratedUsers->sum('value')/$course->ratedUsers()->count();
        $course->rating = $courseRating;
        $course->update();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $courseRating,
            ]]);
    }
}
