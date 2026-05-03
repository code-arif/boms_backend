<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\OrderSession;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\Payment\PaymentRepository;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private PaymentRepository $repo) {}

    /**
     * Record a single payment for one order.
     */
    public function recordPayment(User $admin, array $data): Payment
    {
        $order = Order::findOrFail($data['order_id']);

        abort_if(
            $order->company_id !== $admin->company_id,
            403,
            'Order does not belong to your company.'
        );

        // Prevent duplicate completed payment for same order
        $alreadyPaid = Payment::where('order_id', $order->id)
            ->where('status', 'completed')
            ->exists();

        abort_if($alreadyPaid, 422, 'This order already has a completed payment.');

        $payment = $this->repo->create([
            'company_id'       => $admin->company_id,
            'order_id'         => $order->id,
            'order_session_id' => $order->order_session_id,
            'collected_by'     => $admin->id,
            'amount'           => $data['amount'] ?? $order->total,
            'method'           => $data['method'],
            'status'           => 'completed',
            'reference'        => $data['reference'] ?? null,
            'notes'            => $data['notes'] ?? null,
        ]);

        // Auto-confirm the order upon payment
        $order->update(['status' => 'confirmed']);

        // log audit service
        AuditService::log('payment.recorded', $payment, [], [
            'order_id' => $payment->order_id,
            'amount'   => $payment->amount,
            'method'   => $payment->method,
        ]);

        return $payment->load('order.user:id,name', 'collector:id,name');
    }

    /**
     * Bulk-pay all unpaid orders in a session with a single method.
     */
    public function bulkRecordPayments(User $admin, int $sessionId, string $method): array
    {
        $session = OrderSession::findOrFail($sessionId);

        abort_if(
            $session->company_id !== $admin->company_id,
            403,
            'Session does not belong to your company.'
        );

        $unpaidOrders = $this->repo->unpaidOrders($sessionId);

        abort_if($unpaidOrders->isEmpty(), 422, 'No unpaid orders found for this session.');

        return DB::transaction(function () use ($admin, $session, $unpaidOrders, $method) {

            $payments = [];

            foreach ($unpaidOrders as $order) {
                $payment = $this->repo->create([
                    'company_id'       => $admin->company_id,
                    'order_id'         => $order->id,
                    'order_session_id' => $session->id,
                    'collected_by'     => $admin->id,
                    'amount'           => $order->total,
                    'method'           => $method,
                    'status'           => 'completed',
                ]);

                $order->update(['status' => 'confirmed']);

                $payments[] = $payment;
            }

            return [
                'paid_count' => count($payments),
                'total_paid' => round($unpaidOrders->sum('total'), 2),
                'method'     => $method,
            ];
        });
    }

    /**
     * Mark a payment as refunded.
     */
    public function refund(User $admin, int $paymentId, ?string $reason = null): Payment
    {
        $payment = Payment::where('company_id', $admin->company_id)
            ->where('status', 'completed')
            ->findOrFail($paymentId);

        $payment->update([
            'status' => 'refunded',
            'notes'  => $reason
                ? ($payment->notes . ' | Refund: ' . $reason)
                : $payment->notes,
        ]);

        // Revert order status to pending
        $payment->order()->update(['status' => 'pending']);

        AuditService::log('payment.refunded', $payment, ['status' => 'completed'], ['status' => 'refunded']);

        return $payment->fresh('order.user:id,name');
    }

    /**
     * Session financial summary.
     */
    public function sessionSummary(User $admin, int $sessionId): array
    {
        $session = OrderSession::with('creator:id,name')
            ->findOrFail($sessionId);

        abort_if(
            $session->company_id !== $admin->company_id,
            403,
            'Session does not belong to your company.'
        );

        $totals       = $this->repo->sessionTotals($sessionId);
        $unpaidOrders = $this->repo->unpaidOrders($sessionId);
        $payments     = $this->repo->forSession($sessionId, 200);

        $orderBreakdown = Order::where('order_session_id', $sessionId)
            ->with('user:id,name', 'items.menuItem')
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->map(fn($o) => [
                'order_id'    => $o->id,
                'employee'    => $o->user?->name,
                'total'       => (float) $o->total,
                'status'      => $o->status,
                'items_count' => $o->items->count(),
            ]);

        return [
            'session'        => [
                'id'           => $session->id,
                'title'        => $session->title,
                'date'         => $session->session_date->toDateString(),
                'status'       => $session->status,
                'opened_by'    => $session->creator?->name,
            ],
            'financials'     => $totals,
            'unpaid_count'   => $unpaidOrders->count(),
            'unpaid_orders'  => $unpaidOrders->map(fn($o) => [
                'order_id' => $o->id,
                'employee' => $o->user?->name,
                'total'    => (float) $o->total,
            ]),
            'order_breakdown' => $orderBreakdown,
        ];
    }

    /**
     * Company-wide payment history.
     */
    public function companyHistory(User $admin, int $perPage = 20)
    {
        return Payment::where('company_id', $admin->company_id)
            ->with(['order.user:id,name', 'collector:id,name', 'session'])
            ->latest()
            ->paginate($perPage);
    }
}
