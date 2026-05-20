<?php

namespace App\Http\Controllers\Api\Shift;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Http\Resources\Shift\ShiftResource;
use App\Models\Shift;
use App\Services\Shifts\ShiftService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(
        protected ShiftService $service
    ) {}

    public function index(Request $request)
    {
        return ShiftResource::collection(
            $this->service->getAll($request->all())
        );
    }

    public function store(StoreShiftRequest $request)
    {
        return new ShiftResource(
            $this->service->create($request->validated())
        );
    }

    public function show(Shift $shift)
    {
        return new ShiftResource($shift);
    }

    public function update(UpdateShiftRequest $request, Shift $shift)
    {
        return new ShiftResource(
            $this->service->update($shift, $request->validated())
        );
    }

    public function destroy(Shift $shift)
    {
        $this->service->delete($shift);

        return response()->json([
            'message' => 'Shift deleted successfully.',
        ]);
    }
}