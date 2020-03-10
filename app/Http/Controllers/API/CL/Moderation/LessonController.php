<?php

namespace App\Http\Controllers\API\CL\Moderation;

use App\Http\Requests\CL\LessonRequest;
use App\Models\CL\Lesson;
use App\Models\CL\Rating\RateLesson;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    /**
     * @SWG\Post(
     *     path="/api/cl-moderation/lesson",
     *     summary="Create new lesson.",
     *     tags={"CL (Course)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="position", description="Position of lesson in a list", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="course_id", description="ID of related Course", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="title", description="Title of Article", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body", description="Text of Lesson", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body_short", description="Short text of Lesson", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="scheme", description="Json typed Plan", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="articles", description="Linked ids of articles (Ex: [1,13,99])", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="img_preview", description="Image file", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="video", description="Video file", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="lesson",
     *                      @SWG\Items(ref="#/definitions/Lesson")
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
    public function store(LessonRequest $request)
    {
        $data = $request->all();
        $lesson = Lesson::create($data);

        if ($request->hasFile('img_preview')) {
            $data['img_preview'] = Storage::disk('cl_lesson')->putFileAs(
                $lesson->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        if ($request->hasFile('video')) {
            $data['video'] = Storage::disk('cl_lesson')->putFileAs(
                $lesson->id, $data['video'],
                'video_' . time() . '.' . $data['video']->getClientOriginalExtension()
            );

            $getID3 = new \getID3;
            $file = $getID3->analyze($request->file('video'));
            $data['duration'] = $file['playtime_seconds'];
        }

        $lesson->update($data);

        return response()->json(['success' => true, 'data' => ['lesson' => $lesson]], 200);
    }

    /**
     * @SWG\Put(
     *     path="/api/cl-moderation/lesson/{lesson_id}",
     *     summary="Update lesson.",
     *     tags={"CL (Course)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="position", description="Position of lesson in a list", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="course_id", description="ID of related Course", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="title", description="Title of Article", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body", description="Text of Lesson", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body_short", description="Short text of Lesson", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="scheme", description="Json typed Plan", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="articles", description="Linked ids of articles (Ex: [1,13,99])", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="img_preview", description="Image file", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="video", description="Video file", required=false, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="lesson",
     *                      @SWG\Items(ref="#/definitions/Lesson")
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
    public function update(Lesson $lesson, LessonRequest $request)
    {
        $data = $request->all();

        if ($request->hasFile('img_preview')) {
            Storage::disk('cl_lesson')->delete($lesson->img_preview);
            $data['img_preview'] = Storage::disk('cl_lesson')->putFileAs(
                $lesson->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        if ($request->hasFile('video')) {
            Storage::disk('cl_lesson')->delete($lesson->video);
            $data['video'] = Storage::disk('cl_lesson')->putFileAs(
                $lesson->id, $data['video'],
                'video_' . time() . '.' . $data['video']->getClientOriginalExtension()
            );

            $getID3 = new \getID3;
            $file = $getID3->analyze($request->file('video'));
            $data['duration'] = $file['playtime_seconds'];
        }

        $lesson->update($data);

        return response()->json(['success' => true, 'data' => ['lesson' => $lesson]], 200);
    }

    /**
     * @SWG\Delete(
     *     path="/api/cl-moderation/lesson/{lesson_id}",
     *     summary="Delete lesson.",
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
    public function destroy(Lesson $lesson)
    {
        $response = ['success' => true, 'data' => ['message' => 'Lesson deleted successfully.']];

        try {
            //Storage::disk('cl_lesson')->deleteDirectory($lesson->id);
            $lesson->delete();
        } catch (\Exception $exception) {
            $response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
        }

        return response()->json($response, 200);
    }

    /**
     * @SWG\Post(
     *     path="/api/cl-moderation/lesson/{lesson_id}/rate",
     *     summary="Rate lesson.",
     *     tags={"CL (Course)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="rate_value", description="Rate value (between: 0 and 5)", required=true, in="formData", type="integer",),
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
    public function rate(Lesson $lesson, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate_value' => 'required|numeric|between:0,5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $rateLesson = RateLesson::where('user_id', Auth::id())->where('lesson_id', $lesson->id)->first();
        if (is_null($rateLesson)) {
            RateLesson::create([
                'user_id' => Auth::id(),
                'lesson_id' => $lesson->id,
                'value' => intval($request->get('rate_value')),
            ]);
        } else {
            $rateLesson->update([
                'value' => intval($request->get('rate_value')),
            ]);
        }

        $lessonRating = $lesson->ratedUsers->sum('value')/$lesson->ratedUsers()->count();
        $lesson->rating = $lessonRating;
        $lesson->update();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $lessonRating,
            ]]);
    }
}
