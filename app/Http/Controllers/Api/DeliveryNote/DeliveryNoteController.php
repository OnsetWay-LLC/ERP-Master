<?php

namespace App\Http\Controllers\Api\DeliveryNote;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryNote\StoreDeliveryNoteFromPickListRequest;
use App\Http\Resources\DeliveryNote\DeliveryNoteResource;
use App\Models\DeliveryNote;
use App\Models\PickList;
use App\Services\DeliveryNote\DeliveryNoteService;
use Illuminate\Http\JsonResponse;

class DeliveryNoteController extends Controller
{
    public function __construct(
        private readonly DeliveryNoteService $service
    ) {}

    public function index(): JsonResponse
    {
        $notes = $this->service->getAll();

        return response()->json([
            'data' => DeliveryNoteResource::collection($notes),
        ]);
    }

    public function show(DeliveryNote $deliveryNote): JsonResponse
    {
        $note = $this->service->show($deliveryNote);

        return response()->json([
            'data' => new DeliveryNoteResource($note),
        ]);
    }

    public function storeFromPickList(
        StoreDeliveryNoteFromPickListRequest $request,
        PickList $pickList
    ): JsonResponse {
        $note = $this->service->createFromPickList($pickList);

        return response()->json([
            'message' => 'Delivery note created successfully from pick list.',
            'data' => new DeliveryNoteResource($note),
        ], 201);
    }

    public function submit(DeliveryNote $deliveryNote): JsonResponse
    {
        $note = $this->service->submit($deliveryNote);

        return response()->json([
            'message' => 'Delivery note submitted successfully. Stock has been updated.',
            'data' => new DeliveryNoteResource($note),
        ]);
    }

    public function cancel(DeliveryNote $deliveryNote): JsonResponse
    {
        $note = $this->service->cancel($deliveryNote);

        return response()->json([
            'message' => 'Delivery note cancelled successfully.',
            'data' => new DeliveryNoteResource($note),
        ]);
    }
}