<?php

namespace App\Traits;
use App\Scopes\CompanyScope;

trait HasCompanyScope {
    protected static function bootHasCompanyScope(): void {
        static::addGlobalScope(new CompanyScope());
    }

    public static function withoutCompanyScope(): \Illuminate\Database\Eloquent\Builder {
        return static::withoutGlobalScope(CompanyScope::class);
    }
}
