<?php

namespace App\Http\Controllers\Api\PickList;

use App\Http\Controllers\Controller;
use App\Http\Resources\PickList\PickListResource;
use App\Models\PickList;
use App\Services\PickList\PickListService;
use Illuminate\Http\JsonResponse;

class PickListController extends Controller
{
    public function __construct(
        private readonly PickListService $service
    ) {}

    public function index(): JsonResponse
    {
        $pickLists = $this->service->getAll();

        return response()->json([
            'data' => PickListResource::collection($pickLists),
        ]);
    }

    public function show(PickList $pickList): JsonResponse
    {
        $pickList = $this->service->show($pickList);

        return response()->json([
            'data' => new PickListResource($pickList),
        ]);
    }

    public function cancel(PickList $pickList): JsonResponse
    {
        $pickList = $this->service->cancel($pickList);

        return response()->json([
            'message' => 'Pick list cancelled successfully.',
            'data' => new PickListResource($pickList),
        ]);
    }
}