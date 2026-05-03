<?php
namespace App\Http\Controllers\Api\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\IndexRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use App\Services\Role\RoleService;
use Illuminate\Http\JsonResponse;
class RoleController extends Controller
{
    public function __construct(
        protected RoleService $service
    ) {}

    public function index(IndexRoleRequest $request)
    {
        return RoleResource::collection(
            $this->service->getAll($request->validated())
        );
    }

    public function store(StoreRoleRequest $request)
    {
        return new RoleResource(
            $this->service->create($request->validated())
        );
    }

    public function show(Role $role)
    {
        return new RoleResource(
            $role->load('permissions')
        );
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        return new RoleResource(
            $this->service->update($role, $request->validated())
        );
    }

    public function destroy(Role $role)
    {
        $this->service->delete($role);

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }

    public function permissions()
    {
        return response()->json([
            'data' => $this->service->getPermissions()
        ]);
    }
}