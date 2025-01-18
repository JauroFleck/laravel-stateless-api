<?php

namespace App\Http\Controllers\Admin;

use App\Enums\User\UserProfiles;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AdminController extends Controller
{
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|max:255',
        ]);

        $admin = User::where('email', $request->email)
            ->where('profile', UserProfiles::Admin)->first();

        if (!Hash::check($admin->password, $request->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $admin
        ], HttpResponse::HTTP_OK);
    }
}
