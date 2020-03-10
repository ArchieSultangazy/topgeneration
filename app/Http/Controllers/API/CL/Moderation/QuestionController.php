<?php

namespace App\Http\Controllers\API\CL\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CL\Moderation\QuestionRequest;
use App\Http\Resources\API\CL\Moderation\QuestionResource;
use App\Models\CL\Test;
use App\Models\CL\TestQuestion;
use Illuminate\Http\Response;

class QuestionController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/cl/moderation/questions/{testID}",
     *     summary="Get questions by testID",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lessonTestID", type="integer"),
     *                  @SWG\Property(property="ruName", type="integer"),
     *                  @SWG\Property(property="kkName", type="string"),
     *                  @SWG\Property(property="enName", type="string"),
     *                  @SWG\Property(property="correctAnswerID", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Test doesnt exist",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     * )
     */
    public function index($testID)
    {
        $test = Test::find($testID);

        if (!$test) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Test doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $questions = $test->questions;

        return QuestionResource::collection($questions);
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/moderation/questions/show/{question}",
     *     summary="Get concrete question.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lessonID", type="integer"),
     *                  @SWG\Property(property="creatorID", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Question doesnt exist",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     * )
     */
    public function show($questionID)
    {
        $question = TestQuestion::find($questionID);

        if (!$question) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Question doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        return new QuestionResource($question);
    }

    /**
     * @SWG\Post(
     *     path="/api/cl/moderation/questions",
     *     summary="Store question.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Parameter(name="lesson_test_id",description="test_id",in="formData", required=true, type="number"),
     *     @SWG\Parameter(name="ru_name",description="title in russian",in="formData", required=true, type="string"),
     *     @SWG\Parameter(name="kk_name",description="title in kazakh",in="formData", required=false, type="string"),
     *     @SWG\Parameter(name="en_name",description="title in english",in="formData", required=false, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lessonID", type="integer"),
     *                  @SWG\Property(property="creatorID", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=422, description="Test doesnt exist or test_id is not set.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=500, description="Internal server error. Cannot be saved.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */
    public function store(QuestionRequest $request)
    {
        /** @var TestQuestion $question */
        $question = new TestQuestion($request->mutatorData());

        try {
            $question->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new QuestionResource($question);
    }

    /**
     * @SWG\Patch(
     *     path="/api/cl/moderation/questions/{questionID}",
     *     summary="Update question",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Parameter(name="lesson_test_id",description="id of test",in="formData", required=true, type="number"),
     *     @SWG\Parameter(name="ru_name",description="Title in ru",in="formData", required=true, type="number"),
     *     @SWG\Parameter(name="kk_name",description="Title in kk",in="formData", required=false, type="number"),
     *     @SWG\Parameter(name="en_name",description="Title in en",in="formData", required=false, type="number"),
     *     @SWG\Response(response=200, description="successfull operation",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lessonID", type="integer"),
     *                  @SWG\Property(property="creatorID", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=422, description="Test id or test is not set.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=400, description="Lesson id is not set or lesson doesnt exist",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=500, description="Internal server error. Cannot be saved.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */
    public function update(QuestionRequest $request, TestQuestion $question)
    {
        if (!$question->toArray()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Question doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $question->fill($request->mutatorData());

        try {
            $question->save();
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new QuestionResource($question);
    }

    /**
     * @SWG\Delete(
     *     path="/api/cl/moderation/questions/{questionID}",
     *     summary="Delete question",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Response(response=200, description="successfull operation",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=400, description="Wrong question_id. Not found",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=500, description="Internal server error. Cannot be saved.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */
    public function destroy(TestQuestion $question)
    {
        if (empty($question->toArray())) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Question doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $question->delete();
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'message' => 'Success',
            ],
        ], Response::HTTP_OK);
    }
}
