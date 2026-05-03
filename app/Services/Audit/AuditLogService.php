<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogService
{
    public function query(array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with(['user:id,name,email', 'company:id,name'])
            ->when(isset($filters['company_id']),
                fn($q) => $q->where('company_id', $filters['company_id']))
            ->when(isset($filters['user_id']),
                fn($q) => $q->where('user_id', $filters['user_id']))
            ->when(isset($filters['action']),
                fn($q) => $q->where('action', 'like', '%' . $filters['action'] . '%'))
            ->when(isset($filters['from']),
                fn($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']),
                fn($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->latest()
            ->paginate($perPage);
    }

    public function recentActions(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with(['user:id,name', 'company:id,name'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
