<?php

namespace App\Http\Controllers\API\CL;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TestRequest;
use App\Http\Resources\BriefTestStatisticResource;
use App\Http\Resources\TestResource;
use App\Http\Resources\TestStatisticResource;
use App\Models\CL\Lesson;
use App\Models\CL\Test;
use App\Models\UserResults;
use App\Statistic\Test\Strategies\CollectTestBriefStatisticData;
use App\Statistic\Test\Strategies\CollectTestStatisticData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/cl/test/{lesson_id}",
     *     summary="Get test.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="id", type="integer"),
     *                  @SWG\Property(property="lesson_id", type="integer"),
     *                  @SWG\Property(property="questions", type="array",
     *                      @SWG\Items(
                                @SWG\Property(property="id", type="integer"),
     *                          @SWG\Property(property="ru_name", type="string"),
     *                          @SWG\Property(property="kk_name", type="string"),
     *                          @SWG\Property(property="en_name", type="string"),
     *                          @SWG\Property(property="answers", type="array",
     *                              @SWG\Items(
                                        @SWG\Property(property="id", type="integer"),
         *                              @SWG\Property(property="ru_name", type="string"),
         *                              @SWG\Property(property="kk_name", type="string"),
         *                              @SWG\Property(property="en_name", type="string"),
         *                              @SWG\Property(property="is_correct", type="integer"),
*                                   ),
     *                          ),
*                           ),
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Bad request. Lesson has no tests.",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     * )
     */

    public function show(Lesson $lesson)
    {
        if (!$test = $lesson->tests->first()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Lesson has no tests',
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return new TestResource($test);
    }

    /**
     * @SWG\Post(
     *     path="/api/cl/test/{test_id}",
     *     summary="Start test.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Bad request",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=401, description="Unauthorized",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */
    public function startTest($testID)
    {
        if (!$user = Auth::guard('api')->user()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Unauthorized',
                ],
            ],Response::HTTP_UNAUTHORIZED);
        }

        $test = Test::find($testID);


        if (!$test) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Test doesnt exist'
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $userResults = new UserResults();
        $userResults->user_id = $user->id;
        $userResults->test_id = $test->id;
        $userResults->lesson_id = $test->lesson_id;
        $userResults->save();

        return response()->json([
            'status' => true,
            'data' => [
                'message' => 'Success',
            ]
        ], Response::HTTP_OK);
    }

    /**
     * @SWG\Post(
     *     path="/api/cl/test/{lesson_id}/{test_id}",
     *     summary="End test.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *     @SWG\Parameter(name="result",description="Results of testing in float",in="formData", required=true, type="number"),
     *     @SWG\Parameter(name="correct_questions",description="Ids of correct questions (Ex: [1,14,15])",in="formData", required=true, type="string"),
     *     @SWG\Parameter(name="wrong_questions",description="Ids of wrong questions (Ex: [1,14,15])",in="formData", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *                  @SWG\Property(property="isTestSuccess", type="integer"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=401, description="Unauthorized",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=422, description="Unprocessable entity",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="errors", type="string"),
     *              ),
     *          ),
     *     ),
     * )
     */
    /**
     * @param Request $request
     * @param Lesson $lesson
     * @param Test $test
     * @return \Illuminate\Http\JsonResponse
     */
    public function endTest(TestRequest $request, Lesson $lesson, Test $test)
    {
        if (!$user = Auth::guard('api')->user()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Unauthorized',
                ],
            ],Response::HTTP_UNAUTHORIZED);
        }

        $result = $request->input('result');

        /** @var UserResults $userResults */
        $userResults = UserResults::where([
            ['user_id', $user->id],
            ['test_id', $test->id]
        ])->notFinished()
          ->orderBy('created_at', 'DESC')
          ->first();

        if (!$userResults) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'User result doesnt exist',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $questionsCount = $test->questions()->count();

        $userResults->result = $result;
        $userResults->result_percent = ($result * 100) / $questionsCount;


        $passingScore = (70 * $questionsCount) / 100;

        if ($result >= $passingScore) {
            $userResults->success = 1;

            //Finish lesson
            app('App\Http\Controllers\API\CL\LessonController')->finish($lesson);
        } else {
            $userResults->success = 0;
        }

        $userResults->correct_questions = $request->get('correct_questions');
        $userResults->wrong_questions = $request->get('wrong_questions');

        $userResults->finished_at = (Carbon::now())->toDateTimeString();
        $userResults->try = UserResults::where([['user_id', $user->id], ['test_id', $test->id]])->finished()->count() + 1;
        $userResults->save();


        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'User results has been stored.',
                'isTestSuccess' => $userResults->success,
            ]
        ]);
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/tests/is-success/{lesson_id}",
     *     summary="Check user on success test in lesson.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *      ),
     *      @SWG\Response(response=401, description="Unauthorized",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string"),
     *              ),
     *          ),
     *      ),
     * )
    */

    public function isSuccess(Lesson $lesson)
    {
        if (!$user = Auth::guard('api')->user()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Unauthorized.'
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $result = $lesson->results()->where([['user_id', $user->id], ['success', 1]])->first();

        if (!$result) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'User result is not success',
                ],
            ]);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'User result is success',
                ]
            ]);
        }
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/moderation/tests/statistic/brief/{testID}",
     *     summary="Get brief statistic by test id.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="usersReached", type="integer"),
     *                  @SWG\Property(property="usersComplete", type="integer"),
     *                  @SWG\Property(property="avgTries", type="number"),
     *                  @SWG\Property(property="avgResult", type="number"),
     *                  @SWG\Property(property="avgCompleteTime", type="string"),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Lesson doesnt exist",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=500, description="Internal server error",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     * )
     */
    public function getBriefStatistics(Request $request, $testID)
    {
        $test = Test::find($testID);

        if (!$test) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Test doesnt exist'
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $statisticStrategy = new CollectTestBriefStatisticData($request, $test);

        try {
            $statisticStrategy->run();
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $result = $statisticStrategy->getResult();

        return new BriefTestStatisticResource($result);
    }

    /**
     * @SWG\Get(
     *     path="/api/cl/moderation/tests/statistic/{testID}",
     *     summary="Get statistic by test id.",
     *     tags={"Test"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="data", type="array",
     *                  @SWG\Items(
     *                      @SWG\Property(property="id", type="integer"),
     *                      @SWG\Property(property="userID", type="integer"),
     *                      @SWG\Property(property="testID", type="integer"),
     *                      @SWG\Property(property="lessonID", type="integer"),
     *                      @SWG\Property(property="questionCount", type="integer"),
     *                      @SWG\Property(property="answeredQuestions", type="integer"),
     *                      @SWG\Property(property="answeredQuestionsPercent", type="integer"),
     *                      @SWG\Property(property="try", type="integer"),
     *                      @SWG\Property(property="finishedTime", type="string"),
     *                      @SWG\Property(property="user", type="object",
     *                          @SWG\Property(property="id", type="integer"),
     *                          @SWG\Property(property="userName", type="string"),
     *                          @SWG\Property(property="phone", type="string"),
     *                          @SWG\Property(property="email", type="string"),
     *                          @SWG\Property(property="firstName", type="string"),
     *                          @SWG\Property(property="lastName", type="string"),
     *                          @SWG\Property(property="middleName", type="string"),
     *                          @SWG\Property(property="avatar", type="string"),
     *                      ),
     *                      @SWG\Property(property="finishedAt", type="string"),
     *                      @SWG\Property(property="createdAt", type="string"),
     *                      @SWG\Property(property="updatedAt", type="string"),
     *                      @SWG\Property(property="deletedAt", type="string"),
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Lesson doesnt exist",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     *     @SWG\Response(response=500, description="Internal server error",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="message", type="string")
     *              ),
     *          ),
     *     ),
     * )
     */
    public function getStatistic($testID)
    {
        $test = Test::find($testID);

        if (!$test) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Test doesnt exist'
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $statisticStrategy = new CollectTestStatisticData($test);

        try {
            $statisticStrategy->run();
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'status' => false,
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $result = $statisticStrategy->getResult();

        return TestStatisticResource::collection($result);
    }
}
