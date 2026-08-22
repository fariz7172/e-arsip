<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $validToken = env('API_ACCESS_TOKEN');

        if (!$validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'API Token belum dikonfigurasi di server (.env)'
            ], 500);
        }

        $bearer = $request->bearerToken();
        $token = $request->header('X-API-KEY') ?? $bearer ?? $request->query('token');

        if ($token !== $validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Token API tidak valid.'
            ], 401);
        }

        return $next($request);
    }
}
