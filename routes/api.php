<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello World!']);
});

Route::post('/user/register', function (Request $request) {
    $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8',
    ]);

    try {
        $user = App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'User registration failed', 'message' => $e->getMessage()], 500);
    }

    return response()->json(['user' => $user], 201);
});

// login
Route::post('/user/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = App\Models\User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json(['token' => $token], 200);
});
