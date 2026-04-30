<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuItemRequest;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = MenuItem::latest()->paginate(20);

        return response()->json([
            'status'  => true,
            'message' => 'Menu items retrieved.',
            'data'    => $items,
        ]);
    }

    public function store(Request $request, MenuItemRequest $menuItemRequest): JsonResponse
    {
        $item = MenuItem::create([
            ...$menuItemRequest->validated(),
            'company_id'   => $request->user()->company_id,
            'is_available' => $menuItemRequest->is_available ?? true,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Menu item created.',
            'data'    => $item,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Menu item retrieved.',
            'data'    => MenuItem::findOrFail($id),
        ]);
    }

    public function update(MenuItemRequest $request, int $id): JsonResponse
    {
        $item = MenuItem::findOrFail($id);
        $item->update($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Menu item updated.',
            'data'    => $item->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        MenuItem::findOrFail($id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Menu item deleted.',
            'data'    => null,
        ]);
    }

    public function toggleAvailability(int $id): JsonResponse
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_available' => !$item->is_available]);

        return response()->json([
            'status'  => true,
            'message' => 'Availability updated.',
            'data'    => $item->fresh(),
        ]);
    }
}
