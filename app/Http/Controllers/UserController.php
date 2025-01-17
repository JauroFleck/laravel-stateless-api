<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\NoReturn;
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
    public function destroy(User $user): Response|JsonResponse
    {
        $user->delete();
        return response(status: HttpResponse::HTTP_NO_CONTENT);
    }


    /**
     * Login a user and issue a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], HttpResponse::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken($request->device_name ?? 'auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'device_name' => $request->device_name,
            'user' => new UserResource($user),
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Logout the authenticated user and revoke all their tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var PersonalAccessToken $token */
        $token = auth()->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], HttpResponse::HTTP_OK);
    }
}


