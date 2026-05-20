<?php

namespace App\Http\Controllers\Api\FeesTemplate;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeesTemplate\StoreFeesTemplateRequest;
use App\Http\Requests\FeesTemplate\UpdateFeesTemplateRequest;
use App\Http\Resources\FeesTemplate\FeesTemplateResource;
use App\Models\FeesTemplate;
use App\Services\FeesTemplate\FeesTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class FeesTemplateController extends Controller
{
    public function __construct(
        private readonly FeesTemplateService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getAll(
            $this->companyId(),
            $request->only(['fees_section', 'type', 'is_active', 'per_page'])
        );

        return response()->json([
            'status' => true,
            'data' => FeesTemplateResource::collection($data),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ]);
    }

    public function store(StoreFeesTemplateRequest $request): JsonResponse
    {
        try {
            $feesTemplate = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth()->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'Fees template created successfully.',
                'data' => new FeesTemplateResource($feesTemplate),
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
        $feesTemplate = FeesTemplate::query()
            ->where('company_id', $this->companyId())
            ->with('account')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new FeesTemplateResource($feesTemplate),
        ]);
    }

    public function update(UpdateFeesTemplateRequest $request, int $id): JsonResponse
    {
        $feesTemplate = FeesTemplate::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $updated = $this->service->update(
                $feesTemplate,
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'message' => 'Fees template updated successfully.',
                'data' => new FeesTemplateResource($updated),
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
        $feesTemplate = FeesTemplate::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        $this->service->delete($feesTemplate);

        return response()->json([
            'status' => true,
            'message' => 'Fees template deleted successfully.',
        ]);
    }
}