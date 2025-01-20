<?php

namespace App\Http\Middleware;

use App\Enums\User\UserProfiles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->profile !== UserProfiles::Admin) {
            return response()->json([
                'error' => 'You are not authorized to perform this action',
            ], HttpResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
