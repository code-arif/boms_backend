<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::withoutCompanyScope()
            ->where('email', $credentials['email'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        if (!$user->is_active) {
            return ['success' => false, 'message' => 'Account is deactivated.'];
        }

        // Revoke previous tokens (single-session)
        $user->tokens()->delete();

        $token = $user->createToken('boms-token', [$user->role])->plainTextToken;

        return [
            'success' => true,
            'data'    => [
                'token' => $token,
                'user'  => $user->load('company'),
            ],
        ];
    }
}
