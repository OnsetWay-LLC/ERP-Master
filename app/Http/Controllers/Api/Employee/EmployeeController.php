<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Models\Employee;
use App\Services\Employees\EmployeeService;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $service
    ) {}

    public function index(IndexEmployeeRequest $request)
    {
        return EmployeeResource::collection(
            $this->service->getAll($request->validated())
        );
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Employee created successfully.',
            'data' => new EmployeeResource($employee),
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'message' => 'Employee retrieved successfully.',
            'data' => new EmployeeResource(
                $employee->load([
                    'company',
                    'department',
                    'shifts',
                    'educations',
                    'activeSalary',
                    'allowances',
                    'leaveBalances',
                ])
            ),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee = $this->service->update($employee, $request->validated());

        return response()->json([
            'message' => 'Employee updated successfully.',
            'data' => new EmployeeResource($employee),
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->service->delete($employee);

        return response()->json([
            'message' => 'Employee deleted successfully.',
        ]);
    }
    public function restore(Employee $employee): JsonResponse
    {
        $this->service->restore($employee);

        return response()->json([
            'message' => 'Employee restored successfully.',
        ]);
    }
}