<?php
namespace App\Http\Controllers\Api\Supplier;    

use App\Http\Controllers\Controller;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\Supplier;
use App\Services\Supplier\SupplierService;
use App\Http\Requests\Supplier\IndexSupplierRequest;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;


class SupplierController extends Controller
{
    public function __construct(protected SupplierService $service) {}

    public function index(IndexSupplierRequest $request)
    {
        return SupplierResource::collection(
            $this->service->getAll($request->validated())
        );
    }

    public function store(StoreSupplierRequest $request)
    {
        return new SupplierResource(
            $this->service->create($request->validated())
        );
    }

    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        return new SupplierResource(
            $this->service->update($supplier, $request->validated())
        );
    }

    public function destroy(Supplier $supplier)
    {
        $this->service->delete($supplier);

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}