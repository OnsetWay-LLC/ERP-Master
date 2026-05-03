<?php 
namespace App\Http\Controllers\Api\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Models\Employee;
use App\Services\Employees\EmployeeService;
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

    public function store(StoreEmployeeRequest $request)
    {
        return new EmployeeResource(
            $this->service->create($request->validated())
        );
    }
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        return new EmployeeResource(
            $this->service->update($employee, $request->validated())
        );
    }

    public function show(Employee $employee)
    {
        return new EmployeeResource(
            $employee->load(['company','department','shifts','educations'])
        );
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json(['message' => 'Deleted']);
    }
}