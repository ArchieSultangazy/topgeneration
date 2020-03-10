<?php

namespace App\Http\Middleware\API;

use Closure;

class AtamekenAccess
{
    public function handle($request, Closure $next)
    {
        $server_ip = request()->server('SERVER_ADDR');
        $api_key = $request->header('Api-Key');
        $password = "---" . date('d-m-Y') . "---";

        $decrypted_key = openssl_decrypt($api_key, "AES-128-ECB", $password);

        if ($server_ip == $decrypted_key) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'data' => [
                'errors' => [
                    'user' => 'You don\'t have permission.',
                ]
            ]], 403);
    }
}
