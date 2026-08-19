<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a user and issue the first Bearer token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // The User model's "hashed" cast hashes the plain password on persist.
        $user = User::create($request->validated());
        $token = $user->createToken('frontend')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    /**
     * Authenticate credentials and issue a new Bearer token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $datos = $request->validated();

        // This lookup must happen before authentication exists.
        $user = User::query()->where('email', $datos['email'])->first();

        if ($user === null || ! Hash::check($datos['password'], $user->password)) {
            return response()->json([
                'message' => 'Las credenciales no son válidas.',
            ], 401);
        }

        $token = $user
            ->createToken($datos['device_name'] ?? 'frontend')
            ->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Revoke only the token used for this request.
     */
    public function logout(Request $request): JsonResponse
    {
        $tokenActual = $request->user()->currentAccessToken();

        if ($tokenActual !== null) {
            $tokenActual->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
