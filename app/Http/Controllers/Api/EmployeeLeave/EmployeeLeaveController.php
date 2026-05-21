<?php

namespace App\Http\Controllers\Api\EmployeeLeave;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeLeave\StoreEmployeeLeaveRequest;
use App\Http\Resources\EmployeeLeave\EmployeeLeaveResource;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Services\EmployeeLeave\EmployeeLeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeLeaveController extends Controller
{
    public function __construct(
        private readonly EmployeeLeaveService $service
    ) {}

    public function searchEmployees(Request $request): JsonResponse
    {
        $employees = $this->service->searchEmployees($request->get('search'));

        return response()->json([
            'message' => 'Employees retrieved successfully.',
            'data' => $employees,
        ]);
    }

    public function leaveOptions(Employee $employee): JsonResponse
    {
        return response()->json([
            'message' => 'Employee leave options retrieved successfully.',
            'data' => $this->service->leaveOptions($employee),
        ]);
    }

    public function index()
    {
        return EmployeeLeaveResource::collection(
            EmployeeLeave::query()
                ->with(['employee', 'approver'])
                ->latest('id')
                ->paginate(request('per_page', 10))
        );
    }

    public function store(StoreEmployeeLeaveRequest $request): JsonResponse
    {
        $leave = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Employee leave saved successfully.',
            'data' => new EmployeeLeaveResource($leave),
        ], 201);
    }

    public function show(EmployeeLeave $employeeLeave): JsonResponse
    {
        return response()->json([
            'message' => 'Employee leave retrieved successfully.',
            'data' => new EmployeeLeaveResource(
                $employeeLeave->load(['employee', 'approver'])
            ),
        ]);
    }
}