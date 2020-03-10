<?php

namespace App\Http\Middleware\API;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $accessGroups = Auth::guard('api')->user()->accessGroup->pluck('id')->toArray();
        if (in_array(User::TYPE_ADMIN, $accessGroups)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'data' => [
                'errors' => [
                    'user' => 'This user does not belong to admin group',
                ]
            ]], 403);
    }
}
