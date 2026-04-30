<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignAdminRequest;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;


class CompanyController extends Controller
{
    public function __construct(private CompanyService $service) {}

    public function index(): JsonResponse
    {
        $companies = $this->service->list(perPage: 15);

        return response()->json([
            'status'  => true,
            'message' => 'Companies retrieved.',
            'data'    => CompanyResource::collection($companies)->response()->getData(true),
        ]);
    }

    public function store(CreateCompanyRequest $request): JsonResponse
    {
        $result = $this->service->createWithAdmin($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Company created successfully.',
            'data'    => [
                'company' => new CompanyResource($result['company']),
                'admin'   => [
                    'id'    => $result['admin']->id,
                    'name'  => $result['admin']->name,
                    'email' => $result['admin']->email,
                ],
            ],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $company = $this->service->show($id);

        return response()->json([
            'status'  => true,
            'message' => 'Company retrieved.',
            'data'    => new CompanyResource($company),
        ]);
    }

    public function update(UpdateCompanyRequest $request, int $id): JsonResponse
    {
        $company = $this->service->update($id, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Company updated.',
            'data'    => new CompanyResource($company),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'status'  => true,
            'message' => 'Company deleted.',
            'data'    => null,
        ]);
    }

    public function assignAdmin(AssignAdminRequest $request, int $id): JsonResponse
    {
        $user = $this->service->assignAdmin($id, $request->user_id);

        return response()->json([
            'status'  => true,
            'message' => 'Admin assigned successfully.',
            'data'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'role'    => $user->role,
                'company' => new CompanyResource($user->company),
            ],
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $company = $this->service->toggleStatus($id);

        return response()->json([
            'status'  => true,
            'message' => "Company status changed to {$company->status}.",
            'data'    => new CompanyResource($company),
        ]);
    }
}
