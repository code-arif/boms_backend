<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function placeOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {

            // Validate session is open
            $session = OrderSession::where('status', 'open')
                ->findOrFail($data['order_session_id']);

            abort_if($session->isExpired(), 422, 'This session has expired.');
            abort_if(
                $session->company_id !== $user->company_id,
                403,
                'Session does not belong to your company.'
            );

            // One order per user per session
            $existing = Order::where('user_id', $user->id)
                ->where('order_session_id', $session->id)
                ->first();

            abort_if($existing, 422, 'You have already placed an order for this session.');

            // Build order
            $order = Order::create([
                'company_id'       => $user->company_id,
                'user_id'          => $user->id,
                'order_session_id' => $session->id,
                'status'           => 'pending',
                'notes'            => $data['notes'] ?? null,
                'total'            => 0,
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $menuItem = MenuItem::where('is_available', true)
                    ->findOrFail($item['menu_item_id']);

                $subtotal = $menuItem->price * $item['quantity'];
                $total   += $subtotal;

                $order->items()->create([
                    'company_id'   => $user->company_id,
                    'menu_item_id' => $menuItem->id,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $menuItem->price,
                    'subtotal'     => $subtotal,
                    'notes'        => $item['notes'] ?? null,
                ]);
            }

            $order->update(['total' => $total]);

            return $order->load('items.menuItem', 'session', 'user:id,name');
        });
    }

    public function updateOrder(User $user, int $orderId, array $data): Order
    {
        return DB::transaction(function () use ($user, $orderId, $data) {

            $order = Order::where('user_id', $user->id)
                ->where('status', 'pending')
                ->findOrFail($orderId);

            abort_if($order->session->status !== 'open', 422, 'Session is closed. Cannot modify order.');

            // Replace all items
            $order->items()->delete();

            $total = 0;

            foreach ($data['items'] as $item) {
                $menuItem  = MenuItem::where('is_available', true)->findOrFail($item['menu_item_id']);
                $subtotal  = $menuItem->price * $item['quantity'];
                $total    += $subtotal;

                $order->items()->create([
                    'company_id'   => $user->company_id,
                    'menu_item_id' => $menuItem->id,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $menuItem->price,
                    'subtotal'     => $subtotal,
                    'notes'        => $item['notes'] ?? null,
                ]);
            }

            $order->update([
                'total' => $total,
                'notes' => $data['notes'] ?? $order->notes,
            ]);

            return $order->fresh('items.menuItem', 'session', 'user:id,name');
        });
    }

    public function cancelOrder(User $user, int $orderId): Order
    {
        $order = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->findOrFail($orderId);

        abort_if($order->session->status !== 'open', 422, 'Session is closed. Cannot cancel.');

        $order->update(['status' => 'cancelled']);

        return $order->fresh();
    }

    public function updateStatus(User $admin, int $orderId, string $status): Order
    {
        $allowed = ['pending', 'confirmed', 'delivered', 'cancelled'];
        abort_unless(in_array($status, $allowed), 422, 'Invalid status.');

        $order = Order::findOrFail($orderId);
        $order->update(['status' => $status]);

        return $order->fresh('user:id,name', 'items.menuItem');
    }

    public function myOrders(User $user, int $perPage = 15)
    {
        return Order::where('user_id', $user->id)
            ->with('items.menuItem', 'session')
            ->latest()
            ->paginate($perPage);
    }

    public function sessionOrders(User $admin, int $sessionId, int $perPage = 15)
    {
        $session = OrderSession::findOrFail($sessionId);

        return Order::where('order_session_id', $session->id)
            ->with('user:id,name,email', 'items.menuItem')
            ->latest()
            ->paginate($perPage);
    }
}
