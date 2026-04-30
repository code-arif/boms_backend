<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        $teams = Team::withCount('users')->latest()->paginate(15);

        return response()->json([
            'status'  => true,
            'message' => 'Teams retrieved.',
            'data'    => $teams,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $team = Team::create([
            'company_id'  => $request->user()->company_id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Team created.',
            'data'    => $team,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $team = Team::findOrFail($id);

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $team->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Team updated.',
            'data'    => $team->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Team::findOrFail($id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Team deleted.',
            'data'    => null,
        ]);
    }

    public function assignUser(Request $request, int $teamId): JsonResponse
    {
        $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $team = Team::findOrFail($teamId);
        $user = \App\Models\User::where('company_id', $request->user()->company_id)
            ->findOrFail($request->user_id);

        $user->update(['team_id' => $team->id]);

        return response()->json([
            'status'  => true,
            'message' => 'User assigned to team.',
            'data'    => $user->fresh('team'),
        ]);
    }
}
