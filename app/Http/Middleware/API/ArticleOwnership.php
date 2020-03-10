<?php

namespace App\Http\Middleware\API;

use App\Models\KB\Article;
use Closure;
use Illuminate\Contracts\Auth\Guard;

class ArticleOwnership
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $article_id = $request->segment(count($request->segments()));
        $article = Article::findOrFail($article_id);

        if ($article->user_id !== $this->auth->id()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => [
                        'user' => 'This article does not belong to this user',
                    ]
                ]], 403);
        }

        return $next($request);
    }
}
