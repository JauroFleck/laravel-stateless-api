<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
