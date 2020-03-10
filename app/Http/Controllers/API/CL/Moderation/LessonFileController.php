<?php

namespace App\Http\Controllers\API\CL\Moderation;

use App\Models\CL\LessonFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LessonFileController extends Controller
{
    /**
     * @SWG\Post(
     *     path="/api/cl-moderation/lesson/file",
     *     summary="Create new lesson file.",
     *     tags={"CL (Lesson File)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="lesson_id", description="ID of related Lesson", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="title", description="Title of File", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body", description="Text of File", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="link", description="URL or Upload File", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="file",
     *                      @SWG\Items(ref="#/definitions/LessonFile")
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|numeric',
            'title' => 'required',
            'body' => 'required',
            'link' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $data = $request->all();
        $lessonFile = LessonFile::create($data);

        if (is_file($data['link'])) {
            $data['link'] = Storage::disk('cl_lesson')->putFileAs(
                $request->get('lesson_id'), $data['link'],
                'file_' . $lessonFile->id . '_' . time() . '.' . $data['link']->getClientOriginalExtension()
            );
        }

        $lessonFile->update($data);

        return response()->json(['success' => true, 'data' => ['file' => $lessonFile]], 200);
    }

    /**
     * @SWG\Put(
     *     path="/api/cl-moderation/lesson/file/{file_id}",
     *     summary="Update lesson file.",
     *     tags={"CL (Lesson File)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="lesson_id", description="ID of related Lesson", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="title", description="Title of File", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body", description="Text of File", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="link", description="URL or Upload File", required=false, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="file",
     *                      @SWG\Items(ref="#/definitions/LessonFile")
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
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|numeric',
            'title' => 'required',
            'body' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $data = $request->all();
        $lessonFile = LessonFile::find($id);

        if (is_file($data['link'])) {
            Storage::disk('cl_lesson')->delete($lessonFile->link);
            $data['link'] = Storage::disk('cl_lesson')->putFileAs(
                $request->get('lesson_id'), $data['link'],
                'file_' . $lessonFile->id . '_' . time() . '.' . $data['link']->getClientOriginalExtension()
            );
        }

        $lessonFile->update($data);

        return response()->json(['success' => true, 'data' => ['file' => $lessonFile]], 200);
    }

    /**
     * @SWG\Delete(
     *     path="/api/cl-moderation/lesson/file/{file_id}",
     *     summary="Delete lesson file.",
     *     tags={"CL (Lesson File)"},
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
    public function destroy($id)
    {
        $response = ['success' => true, 'data' => ['message' => 'Lesson file deleted successfully.']];
        $lessonFile = LessonFile::find($id);

        try {
            Storage::disk('cl_lesson')->delete($lessonFile->link);
            $lessonFile->delete();
        } catch (\Exception $exception) {
            $response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
        }

        return response()->json($response, 200);
    }
}
