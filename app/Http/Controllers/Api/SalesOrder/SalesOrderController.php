<?php

namespace App\Http\Controllers\Api\SalesOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
use App\Http\Requests\SalesOrder\UpdateSalesOrderRequest;
use App\Http\Resources\SalesOrder\SalesOrderResource;
use App\Models\SalesOrder;
use App\Services\SalesOrder\SalesOrderService;
use Illuminate\Http\JsonResponse;

class SalesOrderController extends Controller
{
    public function __construct(
        private readonly SalesOrderService $service
    ) {}

    public function index(): JsonResponse
    {
        $orders = $this->service->getAll();

        return response()->json([
            'data' => SalesOrderResource::collection($orders),
        ]);
    }

    public function store(StoreSalesOrderRequest $request): JsonResponse
    {
        $order = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Sales order created successfully.',
            'data' => new SalesOrderResource($order),
        ], 201);
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        $order = $this->service->show($salesOrder);

        return response()->json([
            'data' => new SalesOrderResource($order),
        ]);
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder): JsonResponse
    {
        $order = $this->service->update($salesOrder, $request->validated());

        return response()->json([
            'message' => 'Sales order updated successfully.',
            'data' => new SalesOrderResource($order),
        ]);
    }

    public function destroy(SalesOrder $salesOrder): JsonResponse
    {
        $this->service->delete($salesOrder);

        return response()->json([
            'message' => 'Sales order deleted successfully.',
        ]);
    }

    public function submit(SalesOrder $salesOrder): JsonResponse
    {
        $order = $this->service->submit($salesOrder);

        return response()->json([
            'message' => 'Sales order submitted successfully. Stock reserved and pick list created.',
            'data' => new SalesOrderResource($order),
        ]);
    }

    public function cancel(SalesOrder $salesOrder): JsonResponse
    {
        $order = $this->service->cancel($salesOrder);

        return response()->json([
            'message' => 'Sales order cancelled successfully.',
            'data' => new SalesOrderResource($order),
        ]);
    }
}