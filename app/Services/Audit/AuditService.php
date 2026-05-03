<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(
        string $action,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        $user = Auth::user();

        AuditLog::create([
            'company_id'  => $user?->company_id,
            'user_id'     => $user?->id,
            'action'      => $action,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->id,
            'old_values'  => $oldValues ?: null,
            'new_values'  => $newValues ?: null,
            'ip_address'  => request()->ip(),
        ]);
    }
}
