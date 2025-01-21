<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DenyAuthenticatedMiddleware
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (auth()->check()) {
            return response()->json([
                'error' => 'You are already logged in'
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
