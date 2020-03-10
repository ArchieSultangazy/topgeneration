<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class LastSeen
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('api')->check()) {
            return $next($request);
        }

        $redis = Redis::connection();

        $key = 'last_seen_' . Auth::guard('api')->id();
        $value = (new \DateTime())->format("Y-m-d H:i:s");

        $last_seen = $redis->get('last_seen_' . Auth::guard('api')->id());
        if (!is_null($last_seen)) {
            $progress_key = 'progress_' . Auth::guard('api')->id();
            $progress_time = (new \Carbon\Carbon())->now()->diffInSeconds(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $last_seen));
            if ($progress_time <= 1200) {
                $progress = $progress_time + intval($redis->get('progress_' . Auth::guard('api')->id()));
                $redis->set($progress_key, $progress);
            }
        }

        $redis->set($key, $value);

        return $next($request);
    }
}
