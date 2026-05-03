<?php
namespace App\Http\Controllers\Api\TaxTemplate;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaxTemplate\StoreTaxTemplateRequest;
use App\Http\Requests\TaxTemplate\UpdateTaxTemplateRequest;
use App\Models\TaxTemplate;
use App\Services\TaxTemplate\TaxTemplateService;

class TaxTemplateController extends Controller
{
    public function __construct(
        protected TaxTemplateService $service
    ) {}

    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->getAll()
        ]);
    }

    public function store(StoreTaxTemplateRequest $request)
    {
        $template = $this->service->create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Created successfully',
            'data' => $template
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->getById($id)
        ]);
    }

    public function update(UpdateTaxTemplateRequest $request, $id)
    {
        $template = TaxTemplate::findOrFail($id);

        $updated = $this->service->update($template, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Updated successfully',
            'data' => $updated
        ]);
    }

    public function destroy($id)
    {
        $template = TaxTemplate::findOrFail($id);

        $this->service->delete($template);

        return response()->json([
            'status' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}