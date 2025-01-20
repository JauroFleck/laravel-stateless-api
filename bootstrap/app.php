<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], HttpResponse::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], HttpResponse::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
