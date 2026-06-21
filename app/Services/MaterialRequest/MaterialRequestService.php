<?php

namespace App\Services\MaterialRequest;

use App\Models\Company;
use App\Models\MaterialRequest;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class MaterialRequestService
{
    public function create(array $data): MaterialRequest
    {
        return DB::transaction(function () use ($data) {
            $companyId = Company::query()->value('id');

            $last = MaterialRequest::withTrashed()
                ->latest('id')
                ->first();

            $next = $last ? $last->id + 1 : 1;

            $request = MaterialRequest::create([
                'company_id' => $companyId,
                'request_number' => 'MR' . str_pad($next, 4, '0', STR_PAD_LEFT),
                'request_date' => now()->toDateString(),
                'required_by_date' => $data['required_by_date'] ?? null,
                'status' => 'draft',
                'sent_to_purchase_order_at' => null, 
                'remarks' => $data['remarks'] ?? null,
                'created_by' => auth('api')->id(),
            ]);

           foreach ($data['items'] as $item) {

    $itemModel = Item::findOrFail($item['item_id']);

    $request->items()->create([
        'item_id' => $itemModel->id,
        'barcode' => $itemModel->barcode,
        'warehouse_id' => $item['warehouse_id'],
        'required_by_date' => $item['required_by_date'],
        'required_qty' => $item['required_qty'],
        'ordered_qty' => 0,
        'received_qty' => 0,
        'status' => 'pending',
    ]);

            }

            return $request->fresh()->load([
                'items.item',
                'items.warehouse',
                'creator',
            ]);
        });
    }

    public function submit(MaterialRequest $request): MaterialRequest
    {
        if ($request->status !== 'draft') {
            abort(422, 'Only draft material requests can be submitted.');
        }

        $request->update([
            'status' => 'sent_to_purchase_order',
            'sent_to_purchase_order_at' => now(),
        ]);

        return $request->fresh()->load([
            'items.item',
            'items.warehouse',
            'creator',
        ]);
    }

   public function delete(MaterialRequest $request): void
{
    if (! in_array($request->status, ['draft', 'sent_to_purchase_order'])) {
        abort(422, 'This material request cannot be deleted.');
    }

    if ($request->items()
        ->where('ordered_qty', '>', 0)
        ->exists()
    ) {
        abort(422, 'This material request cannot be deleted because it is linked to a purchase order.');
    }

    $request->delete();
}

    public function cancel(MaterialRequest $request): MaterialRequest
{
    if ($request->status === 'cancelled') {
        abort(422, 'Material request is already cancelled.');
    }

    if (in_array($request->status, [
        'partially_ordered',
        'ordered',
        'completed',
    ])) {
        abort(422, 'This material request cannot be cancelled because it is linked to a purchase order.');
    }

    if ($request->items()
        ->where('ordered_qty', '>', 0)
        ->exists()
    ) {
        abort(422, 'This material request cannot be cancelled because purchase order quantities already exist.');
    }

    $request->update([
        'status' => 'cancelled',
    ]);

    $request->items()->update([
        'status' => 'cancelled',
    ]);

    return $request->fresh()->load([
        'items.item',
        'items.warehouse',
        'creator',
    ]);
}
}