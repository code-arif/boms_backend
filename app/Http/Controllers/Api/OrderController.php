<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    // Employee: place a new order
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $order = $this->service->placeOrder($request->user(), $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Order placed successfully.',
            'data'    => $order,
        ], 201);
    }

    // Employee: view own orders
    public function myOrders(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Your orders retrieved.',
            'data'    => $this->service->myOrders($request->user()),
        ]);
    }

    // Employee: update own pending order
    public function update(PlaceOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->service->updateOrder($request->user(), $id, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Order updated.',
            'data'    => $order,
        ]);
    }

    // Employee: cancel own pending order
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->service->cancelOrder($request->user(), $id);

        return response()->json([
            'status'  => true,
            'message' => 'Order cancelled.',
            'data'    => $order,
        ]);
    }

    // Admin: view all orders in a session
    public function sessionOrders(Request $request, int $sessionId): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Session orders retrieved.',
            'data'    => $this->service->sessionOrders($request->user(), $sessionId),
        ]);
    }

    // Admin: change order status
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,delivered,cancelled'],
        ]);

        $order = $this->service->updateStatus($request->user(), $id, $request->status);

        return response()->json([
            'status'  => true,
            'message' => "Order status updated to {$order->status}.",
            'data'    => $order,
        ]);
    }
}
