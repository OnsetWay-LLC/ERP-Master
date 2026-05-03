<?php
namespace App\Services\Role;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    public function getAll(array $filters)
    {
        $query = Role::with('permissions')
            ->where('guard_name', 'api');

        // search
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'api',
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'],
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function delete(Role $role): void
    {
        // ممكن تضيفي حماية هنا للأدوار الثابتة لاحقًا
        $role->delete();
    }

    public function getPermissions()
    {
        return Permission::where('guard_name', 'api')
            ->orderBy('name')
            ->get();
    }
}