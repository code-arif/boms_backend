<?php

namespace App\Repositories\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository
{
    public function forSession(int $sessionId, int $perPage = 50): LengthAwarePaginator
    {
        return Payment::where('order_session_id', $sessionId)
            ->with(['order.user:id,name', 'collector:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    public function forOrder(int $orderId): Collection
    {
        return Payment::where('order_id', $orderId)
            ->with('collector:id,name')
            ->get();
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function sessionTotals(int $sessionId): array
    {
        $payments = Payment::where('order_session_id', $sessionId)
            ->where('status', 'completed')
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get();

        $collected = $payments->sum('total');

        $sessionTotal = Order::where('order_session_id', $sessionId)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        return [
            'session_total' => round((float) $sessionTotal, 2),
            'collected'     => round((float) $collected, 2),
            'outstanding'   => round((float) ($sessionTotal - $collected), 2),
            'by_method'     => $payments->map(fn($p) => [
                'method' => $p->method,
                'total'  => round((float) $p->total, 2),
                'count'  => $p->count,
            ])->values(),
        ];
    }

    public function unpaidOrders(int $sessionId): Collection
    {
        return Order::where('order_session_id', $sessionId)
            ->whereNotIn('status', ['cancelled'])
            ->whereDoesntHave('payments', fn($q) => $q->where('status', 'completed'))
            ->with('user:id,name,email')
            ->get();
    }
}
