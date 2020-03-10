<?php

namespace App\Http\Middleware\API;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\Models\QA\Comment;

class CommentOwnership
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
		$commentId = $request->segment(count($request->segments()));
		$comment = Comment::findOrFail($commentId);

		if ($comment->user_id !== $this->auth->id()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => [
                        'user' => 'This comment does not belong to this user',
                    ]
                ]], 403);
        }

		return $next($request);
    }
}
