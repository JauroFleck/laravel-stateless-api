<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

Route::apiResource('user', UserController::class);
Route::post('login', [UserController::class, 'login'])->middleware(AuthenticatedMiddleware::class);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
