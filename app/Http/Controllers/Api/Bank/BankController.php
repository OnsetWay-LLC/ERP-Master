<?php

namespace App\Http\Controllers\Api\Bank;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bank\StoreBankRequest;
use App\Http\Requests\Bank\UpdateBankRequest;
use App\Http\Resources\Bank\BankResource;
use App\Models\Bank;
use App\Services\Bank\BankService;
use Illuminate\Http\JsonResponse;

class BankController extends Controller
{
    public function __construct(
        private readonly BankService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function index(): JsonResponse
    {
        $banks = $this->service->getAll($this->companyId());

        return response()->json([
            'status' => true,
            'data' => BankResource::collection($banks),
        ]);
    }

    public function store(StoreBankRequest $request): JsonResponse
    {
        $bank = $this->service->create(
            $request->validated(),
            $this->companyId(),
            auth()->id()
        );

        return response()->json([
            'status' => true,
            'message' => 'Bank created successfully.',
            'data' => new BankResource($bank),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $bank = Bank::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new BankResource($bank),
        ]);
    }

    public function update(UpdateBankRequest $request, int $id): JsonResponse
    {
        $bank = Bank::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        $updated = $this->service->update(
            $bank,
            $request->validated()
        );

        return response()->json([
            'status' => true,
            'message' => 'Bank updated successfully.',
            'data' => new BankResource($updated),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $bank = Bank::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        $this->service->delete($bank);

        return response()->json([
            'status' => true,
            'message' => 'Bank deleted successfully.',
        ]);
    }
}