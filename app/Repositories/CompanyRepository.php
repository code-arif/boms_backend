<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyRepository
{
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return Company::withoutGlobalScopes()
            ->withCount('users')
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): Company
    {
        return Company::withoutGlobalScopes()
            ->withCount('users')
            ->findOrFail($id);
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);
        return $company->fresh();
    }

    public function delete(Company $company): void
    {
        $company->delete();
    }

    public function existsBySlug(string $slug, ?int $excludeId = null): bool
    {
        return Company::withoutGlobalScopes()
            ->where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
