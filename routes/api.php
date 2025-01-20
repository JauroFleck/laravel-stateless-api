<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DenyAuthenticatedMiddleware;
use App\Http\Middleware\DenyUnauthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(DenyUnauthenticatedMiddleware::class)->group(function () {

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::apiResource('users', UserController::class);
    });

    Route::name('users.')->prefix('users')->group(function () {
        Route::post('logout', [UserController::class, 'logout'])->name('logout');
        Route::post('logout-all', [UserController::class, 'logoutFromAllDevices'])->name('logoutAll');
    });

});

Route::middleware(DenyAuthenticatedMiddleware::class)->group(function () {

    Route::post('admin/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('users/login', [UserController::class, 'login'])->name('users.login');

});
