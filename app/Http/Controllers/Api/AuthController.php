<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * User Login: super admin, company admin, employee
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result['success']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
                'data'    => null,
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'data'    => $result['data'],
        ]);
    }

    /**
     * User Logout: super admin, company admin, employee
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully.',
            'data'    => null,
        ]);
    }

    /**
     * Get Authenticated User: super admin, company admin, employee
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Authenticated user.',
            'data'    => [
                'user'    => $request->user()->load('company'),
            ],
        ]);
    }
}
