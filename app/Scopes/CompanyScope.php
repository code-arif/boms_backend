<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope {
    public function apply(Builder $builder, Model $model): void {
        $user = Auth::user();

        if (!$user) return;

        // Super admin sees everything — no scope applied
        if ($user->role === 'super_admin') return;

        if ($user->company_id) {
            $builder->where($model->getTable() . '.company_id', $user->company_id);
        }
    }
}
