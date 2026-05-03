<?php

namespace App\Http\Controllers\Api\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\IndexWarehouseRequest;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\Warehouse\WarehouseResource;
use App\Models\Warehouse;
use App\Services\Warehouse\WarehouseService;

class WarehouseController extends Controller
{
    public function __construct(protected WarehouseService $service) {}

    public function index(IndexWarehouseRequest $request)
    {
        return WarehouseResource::collection(
            $this->service->getAll($request->validated())
        );
    }

    public function store(StoreWarehouseRequest $request)
    {
        return new WarehouseResource(
            $this->service->create($request->validated())
        );
    }

    public function show(Warehouse $warehouse)
    {
        return new WarehouseResource($warehouse);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        return new WarehouseResource(
            $this->service->update($warehouse, $request->validated())
        );
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->service->delete($warehouse);

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}