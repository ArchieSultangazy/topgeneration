<?php

namespace App\Http\Controllers\API\KB\Moderation;

use App\Http\Requests\KB\ArticleRequest;
use App\Models\KB\Article;
use App\Models\KB\Rating\RateArticle;
use App\Services\KB\ArticleService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('article.ownership', ['only' => [
            'update',
            'destroy',
        ]]);
    }

    /**
     * @SWG\Post(
     *     path="/api/kb-moderation/article",
     *     summary="Create new article.",
     *     tags={"KB (Article)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="locale", description="Locale [only: kk, ru, en]", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="is_published", description="Published (0 or 1)", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="title", description="Title of Article", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="themes[]", description="Themes by ID (Ex: 'themes[0] : 1')", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="type", description="Type (can be: 'text', 'video_out' only)", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="img_preview", description="Preview Image", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="text", description="Text (required if type is 'text')", in="formData", type="string",),
     *     @SWG\Parameter(name="video_out", description="Video (required if type is 'video_out')", in="formData", type="string",),
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
    public function store(ArticleRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::id();

        $article = Article::create($data);

        if ($request->hasFile('img_preview')) {
            $data['img_preview'] = Storage::disk('kb_article')->putFileAs(
                $article->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        $article->update(ArticleService::normalizeArticle($article, $data));

        foreach ($request->get('themes') as $item) {
            $article->themes()->attach($item);
        }

        return response()->json(['success' => true, 'data' => ['article' => $article->toArray()]], 200);
    }

    /**
     * @SWG\Put(
     *     path="/api/kb-moderation/article/{article_id}",
     *     summary="Update article.",
     *     tags={"KB (Article)"},
     *     @SWG\Parameter(name="Accept", in="header", description="Format: `application/json`", required=true, type="string"),
     *     @SWG\Parameter(name="Authorization", in="header", description="Format: `Bearer <api_token>`", required=true, type="string"),
     *
     *     @SWG\Parameter(name="locale", description="Locale [only: kk, ru, en]", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="is_published", description="Published (0 or 1)", required=true, in="formData", type="integer",),
     *     @SWG\Parameter(name="title", description="Title of Article", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="themes[]", description="Themes by ID (Ex: 'themes[0] : 1')", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="type", description="Type (can be: 'text', 'video_out' only)", required=true, in="formData", type="string",),
     *     @SWG\Parameter(name="img_preview", description="Preview Image", required=false, in="formData", type="string",),
     *     @SWG\Parameter(name="text", description="Text (required if type is 'text')", in="formData", type="string",),
     *     @SWG\Parameter(name="video_out", description="Video (required if type is 'video_out')", in="formData", type="string",),
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
    public function update(Article $article, ArticleRequest $request)
    {
        $data = $request->all();

        if ($request->hasFile('img_preview')) {
            Storage::disk('kb_article')->delete($article->img_preview);
            $data['img_preview'] = Storage::disk('kb_article')->putFileAs(
                $article->id, $data['img_preview'],
                'img_preview_' . time() . '.' . $data['img_preview']->getClientOriginalExtension()
            );
        }

        $article->update(ArticleService::normalizeArticle($article, $data));

        $article->themes()->detach();
        foreach ($request->get('themes') as $item) {
            $article->themes()->attach($item);
        }

        return response()->json(['success' => true, 'data' => ['article' => $article->toArray()]], 200);
    }

    /**
     * @SWG\Delete(
     *     path="/api/kb-moderation/article/{article_id}",
     *     summary="Delete article.",
     *     tags={"KB (Article)"},
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
    public function destroy(Article $article)
    {
        $response = ['success' => true, 'data' => ['message' => 'Article deleted successfully.']];

        try {
            $article->delete();
        } catch (\Exception $exception) {
            $response = ['success' => false, 'data' => ['message' => $exception->getMessage()]];
        }

        return response()->json($response, 200);

    }

    /**
     * @SWG\Post(
     *     path="/api/kb-moderation/article/{article_id}/rate",
     *     summary="Rate article.",
     *     tags={"KB (Article)"},
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
    public function rate(Article $article, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate_value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['errors' => $validator->errors()]], 422);
        }

        $rateArticle = RateArticle::where('user_id', Auth::id())->where('article_id', $article->id)->first();
        if (is_null($rateArticle)) {
            RateArticle::create([
                'user_id' => Auth::id(),
                'article_id' => $article->id,
                'value' => intval($request->get('rate_value')),
            ]);
        } else {
            $rateArticle->update([
                'value' => intval($request->get('rate_value')),
            ]);
        }

        $articleRating = intval($article->ratedUsers->sum('value'));
        $article->rating = $articleRating;
        $article->update();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $articleRating,
            ]]);
    }
}
