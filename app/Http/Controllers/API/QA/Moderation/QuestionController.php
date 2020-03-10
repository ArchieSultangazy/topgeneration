<?php

namespace App\Http\Controllers\API\QA\Moderation;

use App\Achievement\Strategies\AskQuestionAchievementStrategy;
use App\Achievement\Strategies\DeleteAskQuestionAchievementStrategy;
use App\Entities\Achievement;
use App\Http\Requests\API\QuestionRequest;
use App\Models\QA\Question;
use App\Models\QA\Rating\RateQuestion;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public $successStatus = 200;

	public function __construct()
	{
		$this->middleware('question.ownership', ['only' => [
			'update',
			'destroy',
		]]);
	}

    /**
     * @SWG\Post(
     *     path="/api/qa-moderation/question",
     *     summary="Create new question.",
     *     tags={"QA (Question)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="locale", description="Locale [only: kk, ru, en]", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="title", description="Title of Question", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body", description="Body of Question", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="themes", description="Themes (Ex: '[1, 12, 198]')", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="question",
     *                      @SWG\Items(ref="#/definitions/Question")
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
    public function store(QuestionRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::id();

        $question = new Question();
        $question->fill($data);
        $question->save();

        foreach (json_decode($data['themes']) as $themeId) {
            $question->themes()->attach($themeId);
        }

        $strategy = new AskQuestionAchievementStrategy(\Auth::user());
        $context = new Achievement($strategy);

        try {
            $context->run();
        } catch (\Exception $e) {
            \Log::info($e);
        }

        return response()->json(['success' => true, 'data' => ['question' => $question->toArray()]], $this->successStatus);
    }

    /**
     * @SWG\Put(
     *     path="/api/qa-moderation/question/{question_id}",
     *     summary="Update question.",
     *     tags={"QA (Question)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="locale", description="Locale [only: kk, ru, en]", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="title", description="Title of Question", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="body", description="Body of Question", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="themes", description="Themes (Ex: '[1, 12, 198]')", required=true, in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="question",
     *                      @SWG\Items(ref="#/definitions/Question")
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
    public function update(Question $question, QuestionRequest $request)
    {
        $question->update($request->all());

        $question->themes()->detach();
        foreach (json_decode($request->get('themes')) as $themeId) {
            $question->themes()->attach($themeId);
        }

        return response()->json(['success' => true, 'data' => ['question' => $question->toArray()]], $this->successStatus);
    }

    /**
     * @SWG\Delete(
     *     path="/api/qa-moderation/question/{question_id}",
     *     summary="Delete question.",
     *     tags={"QA (Question)"},
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
    public function destroy(Question $question)
    {
    	$response = ['success' => true, 'data' => ['message' => 'Question deleted successfully.']];

    	try {
			$question->delete();
		} catch (\Exception $exception) {
			$response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
		}
        /** @var User $user */
		$user = Auth::user();

        $context = new Achievement(new DeleteAskQuestionAchievementStrategy($user));

        try {
            $context->run();
        } catch (\Exception $e) {
            \Log::info($e);
        }

        return response()->json($response, $this->successStatus);

    }

    /**
     * @SWG\Post(
     *     path="/api/qa-moderation/question/{question_id}/rate",
     *     summary="Rate question.",
     *     tags={"QA (Question)"},
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
    public function rate(Question $question, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate_value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $rateQuestion = RateQuestion::where('user_id', Auth::id())->where('question_id', $question->id)->first();
        if (is_null($rateQuestion)) {
            RateQuestion::create([
                'user_id' => Auth::id(),
                'question_id' => $question->id,
                'value' => intval($request->get('rate_value')),
            ]);
        } else {
            $rateQuestion->update([
                'value' => intval($request->get('rate_value')),
            ]);
        }

        $questionRating = intval($question->ratedUsers->sum('value'));
        $question->rating = $questionRating;
        $question->update();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $questionRating,
            ]]);
    }
}
