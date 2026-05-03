<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlag extends Model
{
    protected $fillable = ['company_id', 'key', 'enabled', 'description'];

    protected $casts = ['enabled' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if a feature is enabled for a given company.
     */
    public static function isEnabled(int $companyId, string $key): bool
    {
        return static::where('company_id', $companyId)
            ->where('key', $key)
            ->where('enabled', true)
            ->exists();
    }
}
