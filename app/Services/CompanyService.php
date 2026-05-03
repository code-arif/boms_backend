<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FeatureFlag;
use App\Models\User;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyService
{
    public function __construct(private CompanyRepository $repo) {}

    public function list(int $perPage = 15)
    {
        return $this->repo->all($perPage);
    }

    public function show(int $id): Company
    {
        return $this->repo->findById($id);
    }

    /**
     * Create company + first admin atomically.
     */
    public function createWithAdmin(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $company = $this->repo->create([
                'name'   => $data['name'],
                'slug'   => $data['slug'] ?? Str::slug($data['name']),
                'status' => $data['status'] ?? 'active',
                'plan'   => $data['plan'] ?? 'free',
            ]);

            $admin = User::create([
                'company_id' => $company->id,
                'name'       => $data['admin_name'],
                'email'      => $data['admin_email'],
                'password'   => Hash::make($data['admin_password']),
                'role'       => 'company_admin',
                'is_active'  => true,
            ]);

            // Seed default feature flags
            $defaultFlags = ['pwa', 'bulk_pay', 'analytics', 'audit_logs'];
            foreach ($defaultFlags as $flag) {
                FeatureFlag::create([
                    'company_id'  => $company->id,
                    'key'         => $flag,
                    'enabled'     => true, // all on by default
                    'description' => ucfirst(str_replace('_', ' ', $flag)) . ' feature',
                ]);
            }

            return compact('company', 'admin');
        });
    }

    public function update(int $id, array $data): Company
    {
        $company = $this->repo->findById($id);
        return $this->repo->update($company, $data);
    }

    public function delete(int $id): void
    {
        $company = $this->repo->findById($id);
        $this->repo->delete($company);
    }

    /**
     * Assign an existing user as company admin.
     */
    public function assignAdmin(int $companyId, int $userId): User
    {
        $company = $this->repo->findById($companyId);

        $user = User::withoutCompanyScope()->findOrFail($userId);

        abort_if(
            $user->company_id && $user->company_id !== $companyId,
            422,
            'User already belongs to another company.'
        );

        $user->update([
            'company_id' => $company->id,
            'role'       => 'company_admin',
        ]);

        return $user->fresh('company');
    }

    /**
     * Toggle company active/inactive.
     */
    public function toggleStatus(int $id): Company
    {
        $company = $this->repo->findById($id);
        $newStatus = $company->status === 'active' ? 'inactive' : 'active';
        return $this->repo->update($company, ['status' => $newStatus]);
    }
}
