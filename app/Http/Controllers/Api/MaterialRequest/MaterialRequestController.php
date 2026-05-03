<?php
namespace App\Http\Controllers\Api\MaterialRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaterialRequest\StoreMaterialRequestRequest;
use App\Models\MaterialRequest;
use App\Services\MaterialRequest\MaterialRequestService;

class MaterialRequestController extends Controller
{
    public function __construct(
        private MaterialRequestService $service
    ) {}

    public function store(StoreMaterialRequestRequest $request)
    {
        $data = $this->service->create($request->validated());

        return response()->json([
            'status' => true,
            'data' => $data
        ], 201);
    }

    public function submit($id)
    {
        $request = MaterialRequest::findOrFail($id);

        $data = $this->service->submit($request);

        return response()->json([
            'status' => true,
            'message' => 'Submitted successfully',
            'data' => $data
        ]);
    }
    public function destroy($id)
{
    $request = MaterialRequest::findOrFail($id);

    $this->service->delete($request);

    return response()->json([
        'status' => true,
        'message' => 'Deleted successfully'
    ]);
}
public function cancel($id)
{
    $request = MaterialRequest::findOrFail($id);

    $data = $this->service->cancel($request);

    return response()->json([
        'status' => true,
        'message' => 'Cancelled successfully',
        'data' => $data,
    ]);
}
}