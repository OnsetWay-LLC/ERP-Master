<?php

namespace App\Http\Controllers\Api\FinancialYear;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinancialYear\StoreFinancialYearRequest;
use App\Http\Requests\FinancialYear\UpdateFinancialYearRequest;
use App\Http\Resources\FinancialYear\FinancialYearResource;
use App\Models\FinancialYear;
use App\Services\FinancialYear\FinancialYearService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class FinancialYearController extends Controller
{
    public function __construct(
        private readonly FinancialYearService $service
    ) {}

    private function companyId(): int
    {
        return auth('api')->user()->company_id ?? 1;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => FinancialYearResource::collection(
                $this->service->getAll($this->companyId())
            ),
        ]);
    }

    public function store(StoreFinancialYearRequest $request): JsonResponse
    {
        try {
            $year = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth('api')->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Financial year created successfully.',
                'data' => new FinancialYearResource($year),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(FinancialYear $financialYear): JsonResponse
    {
        if ((int) $financialYear->company_id !== $this->companyId()) {
            abort(404);
        }

        return response()->json([
            'status' => true,
            'data' => new FinancialYearResource($financialYear),
        ]);
    }

    public function update(
        UpdateFinancialYearRequest $request,
        FinancialYear $financialYear
    ): JsonResponse {
        if ((int) $financialYear->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $updated = $this->service->update($financialYear, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Financial year updated successfully.',
                'data' => new FinancialYearResource($updated),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function close(FinancialYear $financialYear): JsonResponse
    {
        if ((int) $financialYear->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $closed = $this->service->close($financialYear, auth('api')->id());

            return response()->json([
                'status' => true,
                'message' => 'Financial year closed successfully.',
                'data' => new FinancialYearResource($closed),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reopen(FinancialYear $financialYear): JsonResponse
    {
        if ((int) $financialYear->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $opened = $this->service->reopen($financialYear);

            return response()->json([
                'status' => true,
                'message' => 'Financial year reopened successfully.',
                'data' => new FinancialYearResource($opened),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(FinancialYear $financialYear): JsonResponse
    {
        if ((int) $financialYear->company_id !== $this->companyId()) {
            abort(404);
        }

        try {
            $this->service->delete($financialYear);

            return response()->json([
                'status' => true,
                'message' => 'Financial year deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}