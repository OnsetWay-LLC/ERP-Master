<?php
namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreCompanyAccountSettingRequest;
use App\Http\Resources\Accounting\CompanyAccountSettingResource;
use App\Services\Accounting\CompanyAccountSettingService;
use Illuminate\Http\JsonResponse;

class CompanyAccountSettingController extends Controller
{
    public function __construct(
        private CompanyAccountSettingService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function store(StoreCompanyAccountSettingRequest $request): JsonResponse
    {
        $data = $this->service->set(
            $request->validated(),
            $this->companyId()
        );

        return response()->json([
            'status' => true,
            'message' => 'Default accounts saved successfully',
            'data' => new CompanyAccountSettingResource($data),
        ]);
    }

    public function show(): JsonResponse
    {
        $data = $this->service->get($this->companyId());

        return response()->json([
            'status' => true,
            'data' => new CompanyAccountSettingResource($data),
        ]);
    }
        
}