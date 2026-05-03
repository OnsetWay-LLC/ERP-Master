<?php
namespace App\Services\MaterialRequest;

use App\Models\Company;
use App\Models\MaterialRequest;
use Illuminate\Support\Facades\DB;

class MaterialRequestService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $companyId = Company::query()->value('id');

            $last = MaterialRequest::latest('id')->first();
            $next = $last ? $last->id + 1 : 1;

            $request = MaterialRequest::create([
                'company_id' => $companyId,
                'request_number' => 'MR' . str_pad($next, 4, '0', STR_PAD_LEFT),
                'request_date' => now(),
                'required_by_date' => $data['required_by_date'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $request->items()->create([
                    'item_id' => $item['item_id'],
                    'required_qty' => $item['required_qty'],
                ]);
            }

            return $request->load('items.item');
        });
    }

    // 🔥 submit
    public function submit(MaterialRequest $request)
    {
        if ($request->status !== 'draft') {
            throw new \Exception('Only draft can be submitted');
        }

        $request->update([
            'status' => 'submitted'
        ]);

        return $request->fresh()->load('items.item');
    }
    public function delete(MaterialRequest $request): void
{
    if ($request->status !== 'draft') {
        throw new \Exception('Only draft material requests can be deleted. Use cancel instead.');
    }

    $request->delete();
}
public function cancel(MaterialRequest $request): MaterialRequest
{
    if (in_array($request->status, ['completed', 'cancelled'])) {
        throw new \Exception('Completed or cancelled material requests cannot be cancelled.');
    }

    $request->update([
        'status' => 'cancelled',
    ]);

    $request->items()->update([
        'status' => 'cancelled',
    ]);

    return $request->fresh()->load('items.item', 'warehouse');
}
}