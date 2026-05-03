<?php

namespace App\Http\Controllers\Api\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\IndexDepartmentRequest;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\Department\DepartmentResource;
use App\Models\Department;
use App\Services\Department\DepartmentService;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $service
    ) {}

   public function index(IndexDepartmentRequest $request): JsonResponse
    {
        $departments = $this->service->getAll($request->validated());

        return response()->json([
            'message' => 'Departments retrieved successfully.',
            'data' => DepartmentResource::collection($departments->items()),
            'meta' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
                'from' => $departments->firstItem(),
                'to' => $departments->lastItem(),
            ],
            'links' => [
                'first' => $departments->url(1),
                'last' => $departments->url($departments->lastPage()),
                'prev' => $departments->previousPageUrl(),
                'next' => $departments->nextPageUrl(),
            ],
        ]);
    }

     public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Department created successfully.',
            'data' => new DepartmentResource($department->load('company')),
        ], 201);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json([
            'message' => 'Department retrieved successfully.',
            'data' => new DepartmentResource($department->load('company')),
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->service->update($department, $request->validated());

        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => new DepartmentResource($department),
        ]);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->service->delete($department);

        return response()->json([
            'message' => 'Department deleted successfully',
        ]);
    }
}