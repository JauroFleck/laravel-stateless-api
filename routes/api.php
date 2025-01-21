<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\UserCRUDController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DenyAuthenticatedMiddleware;
use App\Http\Middleware\DenyUnauthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1,api')->group(function () {
    Route::middleware(DenyUnauthenticatedMiddleware::class)->group(function () {

        Route::name('users.')->prefix('users')->group(function () {
            Route::post('logout', [UserAuthController::class, 'logout'])->name('logout');
            Route::post('logout-all', [UserAuthController::class, 'logoutFromAllDevices'])->name('logoutAll');
            Route::post('logout-from-device/{device_id}', [UserAuthController::class, 'logoutFromDevice'])->name('logoutFromDevice');
            Route::get('devices', [UserAuthController::class, 'devices'])->name('devices');
            Route::get('me', [UserAuthController::class, 'me'])->name('me');
            Route::post('send-email-verification', [UserAuthController::class, 'sendEmailVerification'])
                ->middleware('throttle:1,1,email_verification')->name('sendEmailVerification');
            Route::post('verify-email', [UserAuthController::class, 'verifyEmail'])->name('verifyEmail');
        });

        Route::middleware(AdminMiddleware::class)->group(function () {
            Route::apiResource('users', UserCRUDController::class);
        });

    });

    Route::middleware(DenyAuthenticatedMiddleware::class)->group(function () {

        Route::post('admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,5,admin_login')->name('admin.login');

        Route::name('users.')->prefix('users')->group(function () {
            Route::post('login', [UserAuthController::class, 'login'])->middleware('throttle:10,1,user_login')->name('login');
            Route::post('send-reset-token', [UserAuthController::class, 'sendResetToken'])
                ->middleware('throttle:1,1,reset_token')->name('sendResetToken');
            Route::post('reset-password', [UserAuthController::class, 'resetPassword'])->name('resetPassword');
        });

    });
});
