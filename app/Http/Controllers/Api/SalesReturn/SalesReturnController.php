<?php

namespace App\Http\Controllers\Api\SalesReturn;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesReturn\StoreSalesReturnRequest;
use App\Http\Resources\SalesReturn\SalesReturnResource;
use App\Models\SalesReturn;
use App\Services\SalesReturn\SalesReturnService;

class SalesReturnController extends Controller
{
    public function __construct(
        private SalesReturnService $service
    ) {}

    public function store(StoreSalesReturnRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Sales return created successfully as draft.',
            'data' => new SalesReturnResource($data),
        ], 201);
    }

    public function show(SalesReturn $salesReturn)
    {
        return new SalesReturnResource(
            $salesReturn->load([
                'salesInvoice',
                'customer',
                'items.item',
                'items.warehouse',
                'taxes.account',
                'fees.account',
                'journalEntry',
            ])
        );
    }

    public function submit(SalesReturn $salesReturn)
    {
        $data = $this->service->submit($salesReturn);

        return response()->json([
            'message' => 'Sales return submitted successfully.',
            'data' => new SalesReturnResource($data),
        ]);
    }
    public function update(StoreSalesReturnRequest $request, SalesReturn $salesReturn)
{
    $data = $this->service->update($salesReturn, $request->validated());

    return response()->json([
        'message' => 'Sales return updated successfully.',
        'data' => new SalesReturnResource($data),
    ]);
}

public function cancel(SalesReturn $salesReturn)
{
    $data = $this->service->cancel($salesReturn);

    return response()->json([
        'message' => 'Sales return cancelled successfully.',
        'data' => new SalesReturnResource($data),
    ]);
}

public function destroy(SalesReturn $salesReturn)
{
    $this->service->delete($salesReturn);

    return response()->json([
        'message' => 'Sales return deleted successfully.',
    ]);
}
}