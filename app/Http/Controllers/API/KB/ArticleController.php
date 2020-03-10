<?php

namespace App\Http\Controllers\API\KB;

use App\Models\KB\Article;
use App\Models\KB\Comment;
use App\Models\KB\Theme;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * @SWG\Get(
     *     path="/api/kb/article",
     *     summary="Get all articles.",
     *     tags={"KB"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Parameter(name="search", description="Search word", in="formData", type="string",),
     *      @SWG\Parameter(name="per_page", description="Items per page (Pagination)", in="formData", type="integer",),
     *      @SWG\Parameter(name="page", description="Page number (Pagination)", in="formData", type="integer",),
     *     @SWG\Parameter(name="not_published", description="To display not published articles (not_published=1)", in="formData", type="integer",),
     *      @SWG\Parameter(name="themes", description="Themes Ids (Ex: [1,2,3])", in="formData", type="string",),
     *      @SWG\Parameter(name="order_by[column]", description="Custom orderBy (Ex: ?order_by[rating]=DESC&order_by[views]=ASC)", in="formData", type="string",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="articles", type="object",
     *                      @SWG\Property(property="current_page", type="integer"),
     *                      @SWG\Property(property="data",
     *                          @SWG\Items(ref="#/definitions/Article")
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
        $articles = Article::query();
        $locale = $request->get('locale');
        $keyword = $request->get('search');
        $themes = json_decode($request->get('themes'));
        $types = $request->get('types');
        $orderBy = $request->get('order_by');

        if (!is_null($themes)) {
            $articles = $articles->join('kb_theme_articles', 'kb_articles.id', 'kb_theme_articles.article_id')
                ->whereIn('kb_theme_articles.theme_id', $themes)
                ->groupBy('kb_articles.id');
        }
        if (!is_null($locale)) {
            $articles = $articles->where('locale', $locale);
        }
        if ($request->get('not_published') == false) {
            $articles->where('is_published', 1);
        }
        if (!is_null($keyword)) {
            $articles = $articles->where('title', 'LIKE', "%$keyword%");
        }
        if (!is_null($types)) {
            foreach ($types as $type) {
                $articles = $articles->where('type', $type);
            }
        }
        if (!is_null($orderBy)) {
            foreach ($orderBy as $column => $method) {
                if (in_array($column, (new Article())->getFillable())) {
                    $articles = $articles->orderBy($column, $method);
                }
                if ($column == 'comments_count') {
                    $commentIds = Comment::select('article_id', DB::raw("COUNT(*) as count_row"))
                        ->groupBy('article_id')
                        ->orderBy('count_row', $method)
                        ->pluck('article_id')
                        ->toArray();

                    foreach ($commentIds as $id) {
                        $articles = $articles->orderByRaw(DB::raw("id = $id DESC"));
                    }
                }
            }
        }

        $per_page = is_null($request->get('per_page')) ? 20 : intval($request->get('per_page'));
        $articles = $articles->paginate($per_page);

        $articles->map(function ($article) {
            $article->append('author');
            $article->append('themes');
            $article->append('fav_count');
            $article->makeHidden('body');

            return $article;
        });

        return response()->json(['success' => true, 'data' => ['articles' => $articles]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/kb/article/{article_id}",
     *     summary="Get Article.",
     *     tags={"KB"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="article",
     *                      @SWG\Items(ref="#/definitions/Article")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function show(Article $article)
    {
        //increase views count by session
        $session = 'article_' . $article->id;
        if (!Session::has($session)) {
            $article->increment('views');
            Session::put($session, 1);
        }

        if (!Auth::guard('api')->guest()) {
            $redis = Redis::connection();
            $redis_key = 'user_articles_' . Auth::guard('api')->id();
            $user_articles = json_decode($redis->get($redis_key)) ?? [];
            if (!in_array($article->id, $user_articles)) {
                $user_articles[] = $article->id;
            }

            $redis->set($redis_key, json_encode($user_articles));
        }

        $article->append('author');
        $article->append('themes');
        $article->append('comments');
        $article->append('fav_count');

        return response()->json(['success' => true, 'data' => ['article' => $article->toArray()]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/kb/similar-articles/{article_id}",
     *     summary="Get Similar Articles.",
     *     tags={"KB"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="amount", description="Amount of articles to return", in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="articles",
     *                      @SWG\Items(ref="#/definitions/Article")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function similarArticles(Article $article, Request $request)
    {
        $themeIds = $article->themes()->pluck('id')->toArray();
        $articlesQuery = Article::query()->join('kb_theme_articles', 'id', 'article_id')
            ->whereIn('kb_theme_articles.theme_id', $themeIds);

        $articlesAmount = $articlesQuery->get()->unique('id')->count();
        $requestAmount = !is_null($request->get('amount')) ? intval($request->get('amount')) : 5;

        $articles = $articlesQuery->inRandomOrder()
            ->get()
            ->makeHidden('body')
            ->unique('id')
            ->random($articlesAmount < $requestAmount ? $articlesAmount : $requestAmount);

        $articles->map(function ($article) {
            $article->append('author');
            $article->append('themes');
            $article->append('fav_count');

            return $article;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'articles' => $articles->toArray()
            ]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/kb/article/interesting",
     *     summary="Get Interesting Articles.",
     *     tags={"KB"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="date_from", description="Start date (Ex: 2019-06-15)", in="formData", type="string", required=true,),
     *     @SWG\Parameter(name="date_to", description="End date (Ex: 2019-06-15)", in="formData", type="string", required=true,),
     *     @SWG\Parameter(name="limit", description="Limit of articles amount", in="formData", type="integer",),
     *
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="success", type="boolean"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="articles",
     *                      @SWG\Items(ref="#/definitions/Article")
     *                  ),
     *              ),
     *         ),
     *     ),
     * )
     */
    public function interesting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $articles = Article::query()->whereBetween('created_at', [$request->get('date_from'), $request->get('date_to')]);

        if ($request->get('not_published') == false) {
            $articles->where('is_published', 1);
        }

        $articles = $articles->orderBy('views', 'DESC')
            ->take(intval($request->get('limit') ?? 8))
            ->get()
            ->makeHidden('body');

        $articles->map(function ($article) {
            $article->append('themes');
            $article->append('fav_count');

            return $article;
        });

        return response()->json(['success' => true, 'data' => ['articles' => $articles]], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/kb/theme",
     *     summary="Get Themes.",
     *     tags={"KB"},
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
     *                          @SWG\Items(ref="#/definitions/KBTheme")
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
            foreach ($user->favKBThemes()->pluck('id')->toArray() as $id) {
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
     *     path="/api/kb/locale",
     *     summary="Get Locales.",
     *     tags={"KB"},
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
}
