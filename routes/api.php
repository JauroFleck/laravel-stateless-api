<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

Route::apiResource('users', UserController::class);
Route::post('users/login', [UserController::class, 'login'])->middleware(AuthenticatedMiddleware::class)->name('users.login');
Route::post('users/logout', [UserController::class, 'logout'])->middleware('auth:sanctum')->name('users.logout');
