<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $accessGroups = Auth::user()->accessGroup->pluck('id')->toArray();
        if (in_array(User::TYPE_ADMIN, $accessGroups)) {
            return $next($request);
        }

        return redirect()->route('failed');
    }
}
