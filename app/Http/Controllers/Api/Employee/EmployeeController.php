<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Models\Employee;
use App\Models\C;
use App\Services\Employees\EmployeeService;
use Illuminate\Http\JsonResponse;
use App\Imports\EmployeesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Exports\EmployeesExport;
use App\Exports\EmployeeAllowancesExport;
use App\Imports\EmployeeAllowancesImport;
use App\Imports\HrMasterImport;
use App\Exports\HrMasterExport;

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
     public function export()
    {
        return Excel::download(
            new EmployeesExport(),
            'employees.xlsx'
        );
    }

 
 public function exportAllowances()
{
    return Excel::download(
        new EmployeeAllowancesExport(),
        'employee_allowances.xlsx',
        \Maatwebsite\Excel\Excel::XLSX
    );
}

public function importAllowances(Request $request): JsonResponse
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
    ]);

    Excel::import(
        new EmployeeAllowancesImport(),
        $request->file('file')
    );

    return response()->json([
        'status' => true,
        'message' => 'Employee allowances imported successfully.',
    ]);
}
public function exportHrMaster()
{
    return Excel::download(
        new HrMasterExport(),
        'hr_master.xlsx',
        \Maatwebsite\Excel\Excel::XLSX
    );
}

public function importHrMaster(Request $request): JsonResponse
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls'],
    ]);

    Excel::import(
        new HrMasterImport(),
        $request->file('file')
    );

    return response()->json([
        'status' => true,
        'message' => 'HR Master imported successfully.',
    ]);
}

}