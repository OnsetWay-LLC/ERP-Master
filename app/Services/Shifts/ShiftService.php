<?php

namespace App\Services\Shifts;

use App\Models\Shift;

class ShiftService
{
    public function getAll(array $filters)
    {
        $query = Shift::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where('name', 'like', "%{$search}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($data);

        return $shift->fresh();
    }

    public function delete(Shift $shift): void
    {
        $shift->delete();
    }
}