<?php

namespace App\Http\Controllers\API\CL\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CL\Moderation\AnswerRequest;
use App\Http\Resources\API\CL\Moderation\AnswerResource;
use App\Models\CL\TestAnswer;
use App\Models\CL\TestQuestion;
use Illuminate\Http\Response;

class AnswerController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/cl/moderation/answers/{questionID}",
     *     summary="Get answers by questionID",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="questionID", type="integer"),
     *                  @SWG\Property(property="ruName", type="string"),
     *                  @SWG\Property(property="kkName", type="string"),
     *                  @SWG\Property(property="enName", type="string"),
     *                  @SWG\Property(property="isCorrect", type="integer"),
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
    public function index($questionID)
    {
        $question = TestQuestion::find($questionID);

        if (!$question) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Question doesnt exist'
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $answers = $question->answers;

        return AnswerResource::collection($answers);
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/moderation/answers/show/{answerID}",
     *     summary="Get concrete answer.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="questionID", type="integer"),
     *                  @SWG\Property(property="ruName", type="string"),
     *                  @SWG\Property(property="kkName", type="string"),
     *                  @SWG\Property(property="enName", type="string"),
     *                  @SWG\Property(property="isCorrect", type="integer"),
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
    public function show($answerID)
    {
        $answer = TestAnswer::find($answerID);

        if (!$answer) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Answer doesnt exist'
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        return new AnswerResource($answer);
    }

    /**
     * @SWG\Post(
     *     path="/api/cl/moderation/answers/{questionID}",
     *     summary="Store answer.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Parameter(name="question_id",description="test_id",in="formData", required=true, type="number"),
     *     @SWG\Parameter(name="ru_name",description="Title in ru",in="formData", required=true, type="string"),
     *     @SWG\Parameter(name="kk_name",description="Title in kk",in="formData", required=false, type="string"),
     *     @SWG\Parameter(name="en_name",description="Title in en",in="formData", required=false, type="string"),
     *     @SWG\Parameter(name="is_correct",description="Is answer correct?",in="formData", required=true, type="number"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="questionID", type="integer"),
     *                  @SWG\Property(property="ruName", type="string"),
     *                  @SWG\Property(property="kkName", type="string"),
     *                  @SWG\Property(property="enName", type="string"),
     *                  @SWG\Property(property="isCorrect", type="integer"),
     *                  @SWG\Property(property="createdAt", type="string"),
     *                  @SWG\Property(property="updatedAt", type="string"),
     *                  @SWG\Property(property="deletedAt", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=422, description="Question doesnt exist or question_id is not set.",
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
    public function store(AnswerRequest $request)
    {
        $answer = new TestAnswer($request->mutatorData());

        try {
            $answer->save();
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new AnswerResource($answer);
    }

    /**
     * @SWG\Patch(
     *     path="/api/cl/moderation/answers/{answerID}",
     *     summary="Update answer",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Parameter(name="question_id",description="test_id",in="formData", required=true, type="number"),
     *     @SWG\Parameter(name="ru_name",description="Title in ru",in="formData", required=true, type="string"),
     *     @SWG\Parameter(name="kk_name",description="Title in kk",in="formData", required=false, type="string"),
     *     @SWG\Parameter(name="en_name",description="Title in en",in="formData", required=false, type="string"),
     *     @SWG\Parameter(name="is_correct",description="Is answer correct?",in="formData", required=true, type="number"),
     *     @SWG\Response(response=200, description="successfull operation",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="questionID", type="integer"),
     *                  @SWG\Property(property="ruName", type="string"),
     *                  @SWG\Property(property="kkName", type="string"),
     *                  @SWG\Property(property="enName", type="string"),
     *                  @SWG\Property(property="isCorrect", type="integer"),
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
     *     @SWG\Response(response=400, description="Answer doesnt exist or answer_id is not set.",
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
    public function update(AnswerRequest $request, $answerID)
    {
        $answer = TestAnswer::find($answerID);
        if (!$answer) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Answer doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $answer->fill($request->mutatorData());

        try {
            $answer->save();
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new AnswerResource($answer);
    }

    /**
     * @SWG\Delete(
     *     path="/api/cl/moderation/answers/{answerID}",
     *     summary="Delete answer",
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
     *     @SWG\Response(response=400, description="Wrong answer_id. Not found",
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
    public function destroy($answerID)
    {
        $answer = TestAnswer::find($answerID);
        if (!$answer) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Answer doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $answer->delete();
        } catch (\Exception $e) {
            \Log::info($e);
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
