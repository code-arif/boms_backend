<?php

namespace App\Services;

use App\Models\OrderSession;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderSessionService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return OrderSession::withCount('orders')
            ->with('creator:id,name')
            ->latest()
            ->paginate($perPage);
    }

    public function open(User $admin, array $data): OrderSession
    {
        // Only one open session per company per day
        $existing = OrderSession::where('session_date', $data['session_date'] ?? today())
            ->where('status', 'open')
            ->first();

        abort_if($existing, 422, 'An open session already exists for this date.');

        return OrderSession::create([
            'company_id'   => $admin->company_id,
            'created_by'   => $admin->id,
            'title'        => $data['title'] ?? 'Breakfast — ' . now()->format('d M Y'),
            'session_date' => $data['session_date'] ?? today(),
            'closes_at'    => $data['closes_at'] ?? null,
            'status'       => 'open',
        ]);
    }

    public function close(int $sessionId, User $admin): OrderSession
    {
        $session = OrderSession::where('company_id', $admin->company_id)
            ->findOrFail($sessionId);

        abort_if(!$session->isOpen(), 422, 'Session is not open.');

        $session->update(['status' => 'closed']);

        return $session->fresh('orders');
    }

    public function getActive(User $user): ?OrderSession
    {
        return OrderSession::where('status', 'open')
            ->whereDate('session_date', today())
            ->with('orders.items.menuItem')
            ->first();
    }

    public function show(int $id): OrderSession
    {
        return OrderSession::withCount('orders')
            ->with(['orders.user:id,name', 'orders.items.menuItem'])
            ->findOrFail($id);
    }
}
