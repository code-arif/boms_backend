<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FeatureFlag;
use App\Services\Audit\AuditService;
use Illuminate\Support\Collection;

class FeatureFlagService
{
    public function forCompany(int $companyId): Collection
    {
        Company::withoutGlobalScopes()->findOrFail($companyId);

        return FeatureFlag::where('company_id', $companyId)->get();
    }

    public function toggle(int $companyId, string $key): FeatureFlag
    {
        $flag = FeatureFlag::where('company_id', $companyId)
            ->where('key', $key)
            ->firstOrCreate(
                ['company_id' => $companyId, 'key' => $key],
                ['enabled' => false]
            );

        $old = $flag->enabled;
        $flag->update(['enabled' => !$flag->enabled]);

        AuditService::log("feature_flag.{$key}." . ($flag->enabled ? 'enabled' : 'disabled'), $flag, ['enabled' => $old], ['enabled' => $flag->enabled]);

        return $flag->fresh();
    }

    public function set(int $companyId, string $key, bool $enabled): FeatureFlag
    {
        $flag = FeatureFlag::updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['enabled' => $enabled]
        );

        AuditService::log("feature_flag.{$key}." . ($enabled ? 'enabled' : 'disabled'));

        return $flag;
    }

    public function bulkUpdate(int $companyId, array $flags): array
    {
        $results = [];

        foreach ($flags as $key => $enabled) {
            $results[] = $this->set($companyId, $key, (bool) $enabled);
        }

        return $results;
    }
}
