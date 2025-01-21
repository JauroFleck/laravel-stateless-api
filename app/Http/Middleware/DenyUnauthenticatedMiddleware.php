<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DenyUnauthenticatedMiddleware
{
    /**
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (!auth()->check()) {
            throw new AuthenticationException('Unauthenticated');
        }

        return $next($request);
    }
}
