<?php
namespace App\Http\Controllers\Api\StockEntry;

use App\Http\Requests\StockEntry\StockBalanceRequest;
use App\Http\Resources\StockEntry\StockBalanceResource;
use App\Http\Controllers\Controller;
use App\Services\StockEntry\StockReportService;
class StockReportController extends Controller
{
    public function __construct(
        protected StockReportService $service
    ) {
    }

public function stockBalance(StockBalanceRequest $request)
{
    return StockBalanceResource::collection(
        $this->service->stockBalance(
            $request->validated()
        )
    );
}
}