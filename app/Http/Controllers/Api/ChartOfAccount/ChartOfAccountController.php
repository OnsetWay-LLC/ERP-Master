<?php

namespace App\Http\Controllers\Api\ChartOfAccount;

use App\Constants\ChartOfAccountCategories;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChartOfAccount\StoreChartOfAccountRequest;
use App\Http\Requests\ChartOfAccount\UpdateChartOfAccountRequest;
use App\Http\Resources\ChartOfAccount\ChartOfAccountResource;
use App\Models\ChartOfAccount;
use App\Services\ChartOfAccount\ChartOfAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ChartOfAccountController extends Controller
{
    public function __construct(
        private readonly ChartOfAccountService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

  public function index(Request $request): JsonResponse
{
    $filters = $request->only([
        'root_category',
        'sub_category',
        'account_type',
        'account_level',
    ]);

    $filters['root_categories'] = $request->input('root_categories', []);

    $accounts = $this->service->getAll(
        $this->companyId(),
        $filters
    );

    return response()->json([
        'status' => true,
        'data' => ChartOfAccountResource::collection($accounts),
    ]);
}

    public function tree(): JsonResponse
    {
        $tree = $this->service->getTree($this->companyId());

        return response()->json([
            'status' => true,
            'data' => ChartOfAccountResource::collection($tree),
        ]);
    }

    public function store(StoreChartOfAccountRequest $request): JsonResponse
    {
        try {
            $account = $this->service->create(
                $request->validated(),
                $this->companyId(),
                auth()->id()
            );

            return response()->json([
                'status' => true,
                'message' => 'تم إنشاء الحساب بنجاح.',
                'data' => new ChartOfAccountResource($account),
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
        $account = ChartOfAccount::query()
            ->where('company_id', $this->companyId())
            ->with('children')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new ChartOfAccountResource($account),
        ]);
    }

    public function update(UpdateChartOfAccountRequest $request, int $id): JsonResponse
    {
        $account = ChartOfAccount::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $updated = $this->service->update($account, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'تم تعديل الحساب بنجاح.',
                'data' => new ChartOfAccountResource($updated),
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
        $account = ChartOfAccount::query()
            ->where('company_id', $this->companyId())
            ->findOrFail($id);

        try {
            $this->service->delete($account);

            return response()->json([
                'status' => true,
                'message' => 'تم حذف الحساب بنجاح.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function subCategoriesByType(string $accountType): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => ChartOfAccountCategories::subCategoriesByAccountType($accountType),
        ]);
    }
}