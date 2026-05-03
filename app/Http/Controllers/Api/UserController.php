<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $service) {}

    /**
     * Get all users
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->service->list($request->user(), perPage: 15);

        return response()->json([
            'status'  => true,
            'message' => 'Users retrieved.',
            'data'    => $users,
        ]);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->service->create($request->user(), $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'User created.',
            'data'    => $user,
        ], 201);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->service->update($request->user(), $id, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'User updated.',
            'data'    => $user,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);

        return response()->json([
            'status'  => true,
            'message' => 'User deleted.',
            'data'    => null,
        ]);
    }

    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $user = $this->service->toggleActive($request->user(), $id);

        return response()->json([
            'status'  => true,
            'message' => "User " . ($user->is_active ? 'activated' : 'deactivated') . '.',
            'data'    => $user,
        ]);
    }
}
