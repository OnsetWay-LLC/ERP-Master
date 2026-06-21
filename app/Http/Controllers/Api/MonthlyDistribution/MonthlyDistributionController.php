<?php

namespace App\Http\Controllers\Api\MonthlyDistribution;

use App\Http\Controllers\Controller;
use App\Http\Requests\MonthlyDistribution\StoreMonthlyDistributionRequest;
use App\Http\Requests\MonthlyDistribution\UpdateMonthlyDistributionRequest;
use App\Http\Resources\MonthlyDistribution\MonthlyDistributionResource;
use App\Models\MonthlyDistribution;
use App\Services\MonthlyDistribution\MonthlyDistributionService;
use Illuminate\Http\JsonResponse;

class MonthlyDistributionController extends Controller
{
    public function __construct(
        private readonly MonthlyDistributionService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => MonthlyDistributionResource::collection($this->service->getAll()),
        ]);
    }

    public function store(StoreMonthlyDistributionRequest $request): JsonResponse
    {
        $distribution = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Monthly distribution created successfully.',
            'data' => new MonthlyDistributionResource($distribution),
        ], 201);
    }

    public function show(MonthlyDistribution $monthlyDistribution): JsonResponse
    {
        return response()->json([
            'data' => new MonthlyDistributionResource($monthlyDistribution->load('lines')),
        ]);
    }

    public function update(
        UpdateMonthlyDistributionRequest $request,
        MonthlyDistribution $monthlyDistribution
    ): JsonResponse {
        $distribution = $this->service->update($monthlyDistribution, $request->validated());

        return response()->json([
            'message' => 'Monthly distribution updated successfully.',
            'data' => new MonthlyDistributionResource($distribution),
        ]);
    }

    public function destroy(MonthlyDistribution $monthlyDistribution): JsonResponse
    {
        $this->service->delete($monthlyDistribution);

        return response()->json([
            'message' => 'Monthly distribution deleted successfully.',
        ]);
    }
}