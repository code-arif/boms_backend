<?php

namespace App\Services\Impersonation;

use App\Models\User;
use App\Services\Audit\AuditService;

class ImpersonationService
{
    private const TOKEN_NAME = 'impersonation-token';

    /**
     * Super admin starts impersonating a company admin.
     * Returns a scoped token valid for that admin's context.
     */
    public function impersonate(User $superAdmin, int $targetUserId): array
    {
        $target = User::withoutGlobalScopes()
            ->whereIn('role', ['company_admin', 'employee'])
            ->findOrFail($targetUserId);

        // Revoke any previous impersonation tokens
        $superAdmin->tokens()->where('name', self::TOKEN_NAME)->delete();

        // Issue a short-lived impersonation token
        $token = $superAdmin->createToken(self::TOKEN_NAME, [$target->role], now()->addHours(2));

        AuditService::log('impersonation.started', $target, [], [
            'impersonated_user_id' => $target->id,
            'impersonated_role'    => $target->role,
            'company_id'           => $target->company_id,
        ]);

        return [
            'token'  => $token->plainTextToken,
            'target' => [
                'id'         => $target->id,
                'name'       => $target->name,
                'email'      => $target->email,
                'role'       => $target->role,
                'company_id' => $target->company_id,
                'company'    => $target->company?->name,
            ],
            'expires_at' => now()->addHours(2)->toISOString(),
        ];
    }

    /**
     * End impersonation session.
     */
    public function leave(User $superAdmin): void
    {
        $superAdmin->tokens()->where('name', self::TOKEN_NAME)->delete();

        AuditService::log('impersonation.ended');
    }
}