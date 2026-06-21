<?php

namespace App\Http\Controllers\Api\SalesPerson;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesPerson\StoreSalesPersonRequest;
use App\Http\Requests\SalesPerson\UpdateSalesPersonRequest;
use App\Http\Requests\SalesPerson\StoreSalesPersonCommissionRequest;
use App\Http\Resources\SalesPerson\SalesPersonResource;
use App\Models\Company;
use App\Models\SalesPerson;
use App\Services\SalesPerson\SalesPersonService;
use Illuminate\Http\JsonResponse;

class SalesPersonController extends Controller
{
    public function __construct(
        private readonly SalesPersonService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SalesPersonResource::collection($this->service->getAll()),
        ]);
    }

    public function store(StoreSalesPersonRequest $request): JsonResponse
    {
        $salesPerson = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Sales person created successfully.',
            'data' => new SalesPersonResource($salesPerson),
        ], 201);
    }
    public function postCommission(
    StoreSalesPersonCommissionRequest $request
): JsonResponse {
    $companyId = auth('api')->user()->company_id
        ?? Company::query()->value('id');

    if (!$companyId) {
        return response()->json([
            'message' => 'No company found.',
        ], 422);
    }

    $commission = app(
        \App\Services\SalesPerson\SalesPersonCommissionPostingService::class
    )->post(
        $request->validated(),
        (int) $companyId,
        auth('api')->id()
    );

    return response()->json([
        'message' => 'Commission posted successfully.',
        'data' => $commission,
    ]);
}

    public function show(SalesPerson $salesPerson): JsonResponse
    {
        return response()->json([
            'data' => new SalesPersonResource($this->service->show($salesPerson)),
        ]);
    }

    public function update(
        UpdateSalesPersonRequest $request,
        SalesPerson $salesPerson
    ): JsonResponse {
        $salesPerson = $this->service->update($salesPerson, $request->validated());

        return response()->json([
            'message' => 'Sales person updated successfully.',
            'data' => new SalesPersonResource($salesPerson),
        ]);
    }

    public function destroy(SalesPerson $salesPerson): JsonResponse
    {
        $this->service->delete($salesPerson);

        return response()->json([
            'message' => 'Sales person deleted successfully.',
        ]);
    }
   public function restore(int $id): JsonResponse
{
    $salesPerson = $this->service->restore($id);

    return response()->json([
        'message' => 'Sales person restored successfully.',
        'data' => new SalesPersonResource($salesPerson),
    ]);
}
}