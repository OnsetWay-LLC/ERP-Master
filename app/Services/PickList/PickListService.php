<?php

namespace App\Services\PickList;

use App\Models\Company;
use App\Models\PickList;
use RuntimeException;

class PickListService
{
    public function getAll()
    {
        $companyId = Company::query()->firstOrFail()->id;

        return PickList::query()
            ->with([
                'salesOrder',
                'customer',
                'items.warehouse',
                'creator',
            ])
            ->where('company_id', $companyId)
            ->latest()
            ->get();
    }

    public function show(PickList $pickList): PickList
    {
        return $pickList->load([
            'salesOrder',
            'customer',
            'items.item',
            'items.warehouse',
            'creator',
        ]);
    }

    public function cancel(PickList $pickList): PickList
    {
        if ($pickList->status !== 'open') {
            throw new RuntimeException('Only open pick lists can be cancelled.');
        }

        $pickList->update([
            'status' => 'cancelled',
        ]);

        return $pickList->fresh()->load([
            'salesOrder',
            'customer',
            'items.warehouse',
            'creator',
        ]);
    }
}