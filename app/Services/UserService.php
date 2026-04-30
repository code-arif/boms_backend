<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function list(User $authUser, int $perPage = 15): LengthAwarePaginator
    {
        return User::when(
            !$authUser->isSuperAdmin(),
            fn($q) => $q->where('company_id', $authUser->company_id)
                ->where('role', '!=', 'super_admin')
        )
            ->latest()
            ->paginate($perPage);
    }

    public function create(User $authUser, array $data): User
    {
        // Company admins can only create employees in their own company
        if ($authUser->isCompanyAdmin()) {
            $data['company_id'] = $authUser->company_id;
            $data['role']       = 'employee';
        }

        return User::create([
            'company_id' => $data['company_id'],
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'role'       => $data['role'] ?? 'employee',
            'is_active'  => true,
        ]);
    }

    public function update(User $authUser, int $userId, array $data): User
    {
        $user = $this->resolveUser($authUser, $userId);
        $user->update(array_filter($data));
        return $user->fresh();
    }

    public function delete(User $authUser, int $userId): void
    {
        $user = $this->resolveUser($authUser, $userId);
        $user->delete();
    }

    public function toggleActive(User $authUser, int $userId): User
    {
        $user = $this->resolveUser($authUser, $userId);
        $user->update(['is_active' => !$user->is_active]);
        return $user->fresh();
    }

    private function resolveUser(User $authUser, int $userId): User
    {
        $query = User::where('id', $userId);

        if ($authUser->isCompanyAdmin()) {
            $query->where('company_id', $authUser->company_id)
                ->where('role', 'employee');
        }

        return $query->firstOrFail();
    }
}
