<?php

namespace App\Http\Controllers\API\QA\Moderation;

use App\Achievement\Strategies\AnswerQuestionAchievementStrategy;
use App\Achievement\Strategies\DeleteAnswerQuestionAchievementStrategy;
use App\Entities\Achievement;
use App\Http\Requests\API\AnswerRequest;
use App\Models\QA\Answer;
use App\Models\QA\Question;
use App\Models\QA\Rating\RateAnswer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller
{
	public $successStatus = 200;

	public function __construct()
	{
		$this->middleware('answer.ownership', ['only' => [
			'update',
			'destroy',
		]]);
        $this->middleware('question.ownership', ['only' => [
            'selectRightAnswer',
        ]]);
	}

    /**
     * @SWG\Post(
     *     path="/api/qa-moderation/answer",
     *     summary="Create new answer.",
     *     tags={"QA (Answer)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="question_id", description="Question ID", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="body", description="Body of answer", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="answer",
     *                      @SWG\Items(ref="#/definitions/Answer")
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
	public function store(AnswerRequest $request)
	{
	    /** @var User $user */
	    $user = Auth::user();
		$data = $request->all();
		$data['user_id'] = $user->id;

		$answer = new Answer();
		$answer->fill($data);
		$answer->save();

		$strategy = new AnswerQuestionAchievementStrategy($user);
		$context = new Achievement($strategy);

		try {
            $context->run();
        } catch (\Exception $e) {
		    \Log::info($e);
        }

		return response()->json(['success' => true, 'data' => ['answer' => $answer->toArray()]], $this->successStatus);
	}

    /**
     * @SWG\Put(
     *     path="/api/qa-moderation/answer/{answer_id}",
     *     summary="Update answer.",
     *     tags={"QA (Answer)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="body", description="Body of answer", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="answer",
     *                      @SWG\Items(ref="#/definitions/Answer")
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
	public function update(Answer $answer, AnswerRequest $request)
	{
		$answer->update($request->all());

		return response()->json(['success' => true, 'data' => ['answer' => $answer->toArray()]], $this->successStatus);
	}

    /**
     * @SWG\Delete(
     *     path="/api/qa-moderation/answer/{answer_id}",
     *     summary="Delete answer.",
     *     tags={"QA (Answer)"},
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
	public function destroy(Answer $answer)
	{
		$response = ['success' => true, 'data' => ['message' => 'Answer deleted successfully.']];

		try {
			$answer->delete();
		} catch (\Exception $exception) {
			$response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
		}
        /** @var User $user */
        $user = Auth::user();

        $context = new Achievement(new DeleteAnswerQuestionAchievementStrategy($user));

        try {
            $context->run();
        } catch (\Exception $e) {
            \Log::info($e);
        }

		return response()->json($response, $this->successStatus);
	}

    /**
     * @SWG\Post(
     *     path="/api/qa-moderation/answer/{answer_id}/rate",
     *     summary="Rate answer.",
     *     tags={"QA (Answer)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="rate_value", description="Rate value (-1 or 1)", required=true, in="formData", type="integer",),
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
    public function rate(Answer $answer, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate_value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $rateAnswer = RateAnswer::where('user_id', Auth::id())->where('answer_id', $answer->id)->first();
        if (is_null($rateAnswer)) {
            RateAnswer::create([
                'user_id' => Auth::id(),
                'answer_id' => $answer->id,
                'value' => intval($request->get('rate_value')),
            ]);
        } else {
            $rateAnswer->update([
                'value' => intval($request->get('rate_value')),
            ]);
        }

        $answerRating = intval($answer->ratedUsers->sum('value'));
        $answer->rating = $answerRating;
        $answer->update();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $answerRating,
            ]]);
    }

    /**
     * @SWG\Post(
     *     path="/api/qa-moderation/select/{answer_id}/question/{question_id}",
     *     summary="Select correct answer.",
     *     tags={"QA (Answer)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="is_correct", description="Can be 0 or 1", required=true, in="formData", type="integer",),
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
    public function selectRightAnswer(Answer $answer, Question $question, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_correct' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        if (!$question->answers->contains($answer->id)) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => ['answer' => 'This answer does not belong to this question']
                ]], 422);
        }

        $answer->update(['is_correct' => $request->get('is_correct')]);

        return response()->json([
            'success' => true,
            'data' => [
                'is_correct' => $answer->is_correct,
            ]]);
    }
}
