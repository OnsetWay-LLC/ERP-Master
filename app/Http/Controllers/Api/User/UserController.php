<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->service->getAll($request->all());

        return response()->json([
            'message' => 'Users retrieved successfully.',
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());

        return response()->json([
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'User retrieved successfully.',
            'data' => new UserResource($this->service->getById($user)),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->service->delete($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    public function employeeByNationalId(string $nationalId): JsonResponse
    {
        $employee = $this->service->findEmployeeByNationalId($nationalId);

        return response()->json([
            'message' => 'Employee retrieved successfully.',
            'data' => [
                'id' => $employee->id,
                'national_id' => $employee->national_id,
                'full_name' => $employee->full_name,
                'department' => [
                    'id' => $employee->department?->id,
                    'name_ar' => $employee->department?->name_ar,
                    'name_en' => $employee->department?->name_en,
                ],
                'company_email' => $employee->company_email,
                'has_user' => $employee->user()->exists(),
            ],
        ]);
    }
}