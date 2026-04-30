<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpenSessionRequest;
use App\Services\OrderSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderSessionController extends Controller
{
    public function __construct(private OrderSessionService $service) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Sessions retrieved.',
            'data'    => $this->service->list(),
        ]);
    }

    public function open(OpenSessionRequest $request): JsonResponse
    {
        $session = $this->service->open($request->user(), $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Order session opened.',
            'data'    => $session,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Session retrieved.',
            'data'    => $this->service->show($id),
        ]);
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $session = $this->service->close($id, $request->user());

        return response()->json([
            'status'  => true,
            'message' => 'Order session closed.',
            'data'    => $session,
        ]);
    }

    public function active(Request $request): JsonResponse
    {
        $session = $this->service->getActive($request->user());

        return response()->json([
            'status'  => true,
            'message' => $session ? 'Active session found.' : 'No active session.',
            'data'    => $session,
        ]);
    }
}