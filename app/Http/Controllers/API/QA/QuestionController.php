<?php

namespace App\Http\Controllers\API\QA;

use App\Models\QA\Answer;
use App\Models\QA\Question;
use App\Models\QA\Theme;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['only' => [
            'myList',
        ]]);
    }
    /**
     * @SWG\Get(
     *     path="/api/qa/question",
     *     summary="Get all questions.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Parameter(name="search", description="Search word", in="formData", type="string",),
     *      @SWG\Parameter(name="per_page", description="Items per page (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="page", description="Page number (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="themes", description="Themes Ids (Ex: [1,2,3])", in="formData", type="string",),
     *      @SWG\Parameter(name="order_by[column]", description="Custom orderBy (Ex: ?order_by[rating]=DESC&order_by[views]=ASC)", in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="questions", type="object",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Question")
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
        $questions = Question::query();
        $keyword = $request->get('search');
        $themes = json_decode($request->get('themes'));
        $orderBy = $request->get('order_by');

        if (!is_null($themes)) {
            $questions = $questions->join('qa_theme_questions', 'id', 'question_id')
                ->whereIn('qa_theme_questions.theme_id', $themes);
        }
        if (!is_null($keyword)) {
            $questions = $questions->where('title', 'LIKE', "%$keyword%");
        }
        if (!is_null($orderBy)) {
            foreach ($orderBy as $column => $method) {
                $questions = $questions->orderBy($column, $method);
            }
        }

        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $questions = $questions->paginate($per_page);

        $questions->map(function ($question) {
            $question->append('author');
            $question->append('themes');
            $question->append('fav_count');
            $question->append('answers_count');
            return $question;
        });

        return response()->json(['success' => true, 'data' => ['questions' => $questions->toArray()]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/qa/question/{question_id}",
     *     summary="Get Question.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="question",
     *                      @SWG\Items(ref="#/definitions/Question")
     *                  ),
     *                  @SWG\Property(property="answers",
     *                      @SWG\Items(ref="#/definitions/Answer")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function show(Question $question)
    {
        //increase views count by session
        $session = 'question_' . $question->id;
        if (!Session::has($session)) {
            $question->increment('views');
            Session::put($session, 1);
        }

        $question->append('author');
        $question->append('fav_count');
    	$question->append('answers');

        return response()->json(['success' => true, 'data' => ['question' => $question->toArray()]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/qa/interesting",
     *     summary="Get interesting questions.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Parameter(name="search", description="Search word", in="formData", type="string",),
     *      @SWG\Parameter(name="per_page", description="Items per page (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="page", description="Page number (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="themes", description="Themes Ids (Ex: [1,2,3])", in="formData", type="string",),
     *      @SWG\Parameter(name="order_by[column]", description="Custom orderBy (Ex: ?order_by[rating]=DESC&order_by[views]=ASC)", in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="questions", type="object",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Question")
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
    public function interesting(Request $request)
    {
        $questions = Question::query();
        $keyword = $request->get('search');
        $themes = json_decode($request->get('themes'));
        $orderBy = $request->get('order_by');

        if (!is_null($themes)) {
            $questions = $questions->join('qa_theme_questions', 'id', 'question_id')
                ->whereIn('qa_theme_questions.theme_id', $themes);
        }
        if (!is_null($keyword)) {
            $questions = $questions->where('title', 'LIKE', "%$keyword%");
        }
        if (!is_null($orderBy)) {
            foreach ($orderBy as $column => $method) {
                $questions = $questions->orderBy($column, $method);
            }
        }

        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $questions = $questions->paginate($per_page);

        $questions->map(function ($question) {
            $question->append('author');
            $question->append('themes');
            $question->append('fav_count');
            $question->append('answers_count');
            return $question;
        });

        return response()->json(['success' => true, 'data' => ['questions' => $questions->toArray()]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/qa/my-list",
     *     summary="Get user's questions.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Parameter(name="search", description="Search word", in="formData", type="string",),
     *      @SWG\Parameter(name="per_page", description="Items per page (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="page", description="Page number (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="themes", description="Themes Ids (Ex: [1,2,3])", in="formData", type="string",),
     *      @SWG\Parameter(name="order_by[column]", description="Custom orderBy (Ex: ?order_by[rating]=DESC&order_by[views]=ASC)", in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="questions", type="object",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Question")
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
    public function myList(Request $request)
    {
        $keyword = $request->get('search');
        $themesIds = Auth::user()->favQAThemes()->pluck('id');
        $questions = Question::query()->join('qa_theme_questions', 'id', 'question_id')
            ->whereIn('qa_theme_questions.theme_id', $themesIds);
        $themes = json_decode($request->get('themes'));
        $orderBy = $request->get('order_by');

        if (!is_null($themes)) {
            $questions = $questions->whereIn('qa_theme_questions.theme_id', $themes);
        }
        if (!is_null($keyword)) {
            $questions = $questions->where('title', 'LIKE', "%$keyword%");
        }
        if (!is_null($orderBy)) {
            foreach ($orderBy as $column => $method) {
                $questions = $questions->orderBy($column, $method);
            }
        }

        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $questions = $questions->paginate($per_page);

        $questions->map(function ($question) {
            $question->append('author');
            $question->append('themes');
            $question->append('fav_count');
            $question->append('answers_count');
            return $question;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions->toArray(),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/qa/answers-leader",
     *     summary="Get Answer Leaders.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="leaders", type="object"),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function answersLeader()
    {
        $answers = DB::table('qa_answers')
            ->select('user_id', DB::raw('count(*) as answers'), DB::raw('sum(rating) as rating'))
            ->groupBy('user_id')
            ->orderBy('rating', 'DESC')
            ->orderBy('answers');

        $answerUsers = $answers->pluck('user_id')->toArray();
        $answerUsersOrder = implode(',', $answerUsers);
        $answerRatings = $answers->get()->keyBy('user_id')->toArray();

        if (empty($answerUsers)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'leaders' => []
                ]], 200);
        }

        $leaders = User::whereIn('id', $answerUsers)->orderByRaw(DB::raw("FIELD(id, $answerUsersOrder)"))->get();
        $leaders->map(function ($user) use ($answerRatings) {
           $user->append('answers_count');
           $user->append('comments_count');
           $user['rating_count'] = $answerRatings[$user->id]->rating;

           return $user;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'leaders' => $leaders->toArray()
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/qa/similar-questions/{question_id}",
     *     summary="Get Similar Questions.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="amount", description="Amount of questions to return", in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="questions",
     *                      @SWG\Items(ref="#/definitions/Question")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function similarQuestions(Question $question, Request $request)
    {
        $themeIds = $question->themes()->pluck('id')->toArray();
        $questionsQuery = Question::query()->join('qa_theme_questions', 'id', 'question_id')
            ->whereIn('qa_theme_questions.theme_id', $themeIds);

        $questionsAmount = $questionsQuery->get()->unique('id')->count();
        $requestAmount = !is_null($request->get('amount')) ? intval($request->get('amount')) : 5;

        $questions = $questionsQuery->inRandomOrder()
            ->get()
            ->unique('id')
            ->random($questionsAmount < $requestAmount ? $questionsAmount : $requestAmount);

        $questions->map(function ($question) {
            $question->append('author');
            return $question;
        });


        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions->toArray()
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/qa/theme",
     *     summary="Get Themes.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=false, type="string"),
     *
     *     @SWG\Parameter(name="search", description="Search word", in="formData", type="string",),
     *     @SWG\Parameter(name="per_page", description="Page number per page", in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="themes",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Theme")
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
    public function getThemes(Request $request)
    {
        $user = Auth::guard('api')->user();
        $keyword = $request->get('search');
        $themes = Theme::query();

        if (!is_null($user)) {
            foreach ($user->favQAThemes()->pluck('id')->toArray() as $id) {
                $themes = $themes->orderByRaw(DB::raw("id = $id DESC"));
            }
        }
        if (!is_null($keyword)) {
            $themes = $themes->where('name', 'LIKE', "%$keyword%");
        }

        $themes = $themes->paginate($request->get('per_page') ?? 10);

        return response()->json([
            'success' => true,
            'data' => [
                'themes' => $themes->toArray(),
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/qa/locale",
     *     summary="Get Locales.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="locales", type="object"),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getLocales()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'locales' => config('app.locales')
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/qa/count",
     *     summary="Get QA Count.",
     *     tags={"QA"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="questions", type="integer"),
     *                  @SWG\Property(property="answers", type="integer"),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function getQACount()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'questions' => Question::all()->count(),
                'answers' => Answer::all()->count(),
            ]], 200);
    }
}
