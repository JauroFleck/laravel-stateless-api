<?php

namespace App\Http\Controllers;

use App\Enums\User\UserProfiles;
use App\Http\Resources\User\TokenResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return UserResource::collection(User::paginate($request->get('per_page', 10)));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], HttpResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json([], HttpResponse::HTTP_NO_CONTENT);
    }

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
}


