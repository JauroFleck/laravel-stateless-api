<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('users', UserController::class);
    Route::name('users.')->prefix('users')->group(function () {
        Route::post('logout', [UserController::class, 'logout'])->name('logout');
        Route::post('logout-all', [UserController::class, 'logoutFromAllDevices'])->name('logoutAll');
    });

});

Route::middleware(AuthenticatedMiddleware::class)->group(function () {

    Route::post('users/login', [UserController::class, 'login'])->name('users.login');

});


