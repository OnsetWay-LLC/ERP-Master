<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreJournalEntryRequest;
use App\Http\Resources\Accounting\JournalEntryResource;
use App\Services\Accounting\JournalEntryService;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class JournalEntryController extends Controller
{
    public function __construct(
        private readonly JournalEntryService $service
    ) {
    }

   public function index(): JsonResponse
{
    $entries = $this->service->getAll($this->companyId());

    return response()->json([
        'status' => true,
        'data' => JournalEntryResource::collection($entries),
    ]);
}

    public function show(int $id): JsonResponse
    {
        $entry = $this->service->show($this->companyId(), $id);

        return response()->json([
            'status' => true,
            'data' => new JournalEntryResource($entry),
        ]);
    }

   public function store(StoreJournalEntryRequest $request): JsonResponse
{
    $user = auth('api')->user();

    $entry = $this->service->create(
        data: $request->validated(),
        companyId: $this->companyId(),
        userId: $user->id
    );

    return response()->json([
        'status' => true,
        'message' => 'Journal entry draft created successfully.',
        'data' => new JournalEntryResource($entry),
    ], 201);
}
    public function update(StoreJournalEntryRequest $request, int $id): JsonResponse
    {
        $user = auth('api')->user();

        $entry = $this->service->updateDraft(
            companyId: $this->companyId(),
            id: $id,
            data: $request->validated()
        );

        return response()->json([
            'status' => true,
            'message' => 'Journal entry draft updated successfully.',
            'data' => new JournalEntryResource($entry),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteDraft($this->companyId(), $id);

        return response()->json([
            'status' => true,
            'message' => 'Journal entry draft deleted successfully.',
        ]);
    }

   public function submit(int $id): JsonResponse
{
    $entry = $this->service->submit(
        companyId: $this->companyId(),
        id: $id
    );

    return response()->json([
        'status' => true,
        'message' => 'Journal entry posted successfully.',
        'data' => new JournalEntryResource($entry),
    ]);
}

    public function cancel(int $id): JsonResponse
    {
        $companyId = auth('api')->user()->company_id;

        $entry = $this->service->cancel($this->companyId(), $id);

        return response()->json([
            'status' => true,
            'message' => 'Journal entry cancelled successfully and reversal entry created.',
            'data' => new JournalEntryResource($entry),
        ]);
    }

   public function accountsDropdown(): JsonResponse
{
    $companyId = auth('api')->user()->company_id 
        ?? Company::query()->value('id');

    $accounts = $this->service->getAccountsDropdown((int) $companyId);

    return response()->json([
        'status' => true,
        'data' => $accounts,
    ]);
}
private function companyId(): int
{
    $companyId = auth('api')->user()->company_id ?? Company::query()->value('id');

    if (! $companyId) {
        abort(422, 'No company found.');
    }

    return (int) $companyId;
}
}