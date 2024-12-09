<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JwtHelper;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not provided'
            ], 401);
        }

        $token = substr($authHeader, 7);
        $claims = JwtHelper::validateToken($token);

        if (!$claims) {
            return response()->json([
                'status'=> 'error',
                'message'=> 'Invalid or expired token'
            ], 401);
        }

        // Attach claims to the request
        $request->attributes->add(['jwr_claims' => $claims]);

        return $next($request);
    }
}
