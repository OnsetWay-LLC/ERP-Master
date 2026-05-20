<?php

namespace App\Http\Controllers\Api\BankAccount;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccount\StoreBankAccountRequest;
use App\Http\Requests\BankAccount\UpdateBankAccountRequest;
use App\Http\Resources\BankAccount\BankAccountResource;
use App\Models\BankAccount;
use App\Services\BankAccount\BankAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BankAccountController extends Controller
{
    public function __construct(
        private readonly BankAccountService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function index(Request $request): JsonResponse
    {
        $accounts = $this->service->getAll(
            $this->companyId(),
            $request->only(['bank_id'])
        );

        return response()->json([
            'status' => true,
            'data' => BankAccountResource::collection($accounts),
        ]);
    }

    public function store(StoreBankAccountRequest $request): JsonResponse
    {
        try {
            $account = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth()->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Bank account created successfully.',
                'data' => new BankAccountResource($account),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $account = BankAccount::query()
            ->where('company_id', $this->companyId())
            ->with(['bank', 'chartAccount'])
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new BankAccountResource($account),
        ]);
    }

    public function update(UpdateBankAccountRequest $request, int $id): JsonResponse
    {
        $account = BankAccount::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $updated = $this->service->update($account, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Bank account updated successfully.',
                'data' => new BankAccountResource($updated),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $account = BankAccount::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        $this->service->delete($account);

        return response()->json([
            'status' => true,
            'message' => 'Bank account deleted successfully.',
        ]);
    }
}