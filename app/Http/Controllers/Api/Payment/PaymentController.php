<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\BulkPaymentRequest;
use App\Http\Requests\Payment\RecordPaymentRequest;
use App\Http\Resources\Payment\PaymentResource;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service) {}

    /**
     * Record payment for a single order.
     */
    public function store(RecordPaymentRequest $request): JsonResponse
    {
        $payment = $this->service->recordPayment(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'status'  => true,
            'message' => 'Payment recorded.',
            'data'    => new PaymentResource($payment),
        ], 201);
    }

    /**
     * Bulk-pay all unpaid orders in a session.
     */
    public function bulkRecord(BulkPaymentRequest $request, int $sessionId): JsonResponse
    {
        $result = $this->service->bulkRecordPayments(
            $request->user(),
            $sessionId,
            $request->method
        );

        return response()->json([
            'status'  => true,
            'message' => "{$result['paid_count']} orders marked as paid.",
            'data'    => $result,
        ]);
    }

    /**
     * Refund a completed payment.
     */
    public function refund(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:300'],
        ]);

        $payment = $this->service->refund(
            $request->user(),
            $id,
            $request->reason
        );

        return response()->json([
            'status'  => true,
            'message' => 'Payment refunded.',
            'data'    => new PaymentResource($payment),
        ]);
    }

    /**
     * Full financial summary for a session.
     */
    public function sessionSummary(Request $request, int $sessionId): JsonResponse
    {
        $summary = $this->service->sessionSummary($request->user(), $sessionId);

        return response()->json([
            'status'  => true,
            'message' => 'Session summary retrieved.',
            'data'    => $summary,
        ]);
    }

    /**
     * Payments for a specific order.
     */
    public function forOrder(Request $request, int $orderId): JsonResponse
    {
        $payments = $this->service->forOrder($orderId);

        return response()->json([
            'status'  => true,
            'message' => 'Order payments retrieved.',
            'data'    => PaymentResource::collection($payments),
        ]);
    }

    /**
     * Company-wide payment history.
     */
    public function history(Request $request): JsonResponse
    {
        $history = $this->service->companyHistory($request->user());

        return response()->json([
            'status'  => true,
            'message' => 'Payment history retrieved.',
            'data'    => PaymentResource::collection($history)->response()->getData(true),
        ]);
    }
}