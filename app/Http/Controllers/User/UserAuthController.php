<?php

namespace App\Http\Controllers\User;

use App\Enums\User\UserProfiles;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\TokenResource;
use App\Http\Resources\User\UserResource;
use App\Mail\SendEmailVerificationToken;
use App\Mail\SendResetToken;
use App\Models\User\EmailVerificationToken;
use App\Models\User\PasswordResetToken;
use App\Models\User\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UserAuthController extends Controller
{
    /**
     * Login a user and issue a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|max:255',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $credentials['email'])
            ->where('profile', UserProfiles::Patient)->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => 'Invalid credentials',
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'device_name' => $request->device_name,
            'user' => new UserResource($user),
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Logout the authenticated user and revoke all their tokens.
     */
    public function logout(): JsonResponse
    {
        /** @var PersonalAccessToken $token */
        $token = auth()->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Logout the authenticated user from all devices by revoking all their tokens.
     */
    public function logoutFromAllDevices(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully',
        ], HttpResponse::HTTP_OK);
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function devices(): AnonymousResourceCollection
    {
        $tokens = auth()->user()->tokens;
        return TokenResource::collection($tokens);
    }

    /**
     * @param int $device_id
     * @return JsonResponse
     */
    public function logoutFromDevice(int $device_id): JsonResponse
    {
        $token = auth()->user()->tokens()->where('id', $device_id)->first();

        if (!$token) {
            return response()->json([
                'error' => 'Device not found.',
            ], HttpResponse::HTTP_NOT_FOUND);
        }

        $device_name = $token->name;
        $token->delete();

        return response()->json([
            'message' => 'Logged out from device successfully',
            'device' => $device_name,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * @return UserResource
     */
    public function me(): UserResource
    {
        return new UserResource(auth()->user());
    }

    /**
     * @return void
     */
    public function sendResetToken(Request $request): JsonResponse
    {
        $request->validate([ 'email' => 'required|email' ]);

        if ($user = User::where('email', $request->email)->first()) {
            Mail::to($user)->send(new SendResetToken(
                PasswordResetToken::updateOrCreate([
                    'email' => $user->email
                ], [
                    'token' => rand(100000, 999999),
                    'created_at' => now(),
                    'used' => false,
                ])
            ));
        }

        return response()->json([ 'message' => 'Submitted' ], HttpResponse::HTTP_OK);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $passwordResetToken = PasswordResetToken::where('email', $request->email)
            ->where('token', $request->token)
            ->where('used', false)
            ->where('created_at', '>', now()->subMinutes(5))->first();

        if (!$passwordResetToken) {
            return response()->json([
                'error' => 'Invalid credentials/Expired token'
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }

        $passwordResetToken->user()->update([ 'password' => Hash::make($request->password) ]);
        $passwordResetToken->update(['used' => true]);

        return response()->json([ 'message' => 'Submitted' ], HttpResponse::HTTP_OK);
    }

    /**
     * @return JsonResponse
     */
    public function sendEmailVerification(): JsonResponse
    {
        $user = auth()->user();
        if ($user->email_verified_at !== null) {
            return response()->json([
                'message' => 'Already verified',
            ], HttpResponse::HTTP_OK);
        }

        Mail::to($user)->send(new SendEmailVerificationToken(
            EmailVerificationToken::updateOrCreate([
                'user_id' => $user->id
            ], [
                'token' => rand(100000, 999999),
                'created_at' => now(),
            ])
        ));

        return response()->json([
            'message' => 'Submitted'
        ], HttpResponse::HTTP_OK);
    }

    /**
     * @param EmailVerificationRequest $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([ 'token' => 'required' ]);

        $user = auth()->user();

        if ($user->email_verified_at !== null) {
            return response()->json([
                'message' => 'Already verified',
            ], HttpResponse::HTTP_OK);
        }

        $emailVerificationToken = EmailVerificationToken::where('user_id', $user->id)
            ->where('token', $request->token)
            ->where('created_at', '>', now()->subMinutes(5))->first();

        if (!$emailVerificationToken) {
            return response()->json([
                'error' => 'Invalid/Expired token'
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }

        $emailVerificationToken->user->update([ 'email_verified_at' => now() ]);

        return response()->json([ 'message' => 'Submitted' ], HttpResponse::HTTP_OK);
    }
}
