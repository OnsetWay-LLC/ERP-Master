<?php

namespace App\Http\Controllers\Api\DeliveryNote;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryNote\StoreDeliveryNoteFromPickListRequest;
use App\Http\Resources\DeliveryNote\DeliveryNoteResource;
use App\Models\DeliveryNote;
use App\Models\PickList;
use App\Services\DeliveryNote\DeliveryNoteService;
use Illuminate\Http\JsonResponse;
use App\Models\Company;
use Mpdf\Mpdf;

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
    public function pdf(DeliveryNote $deliveryNote)
{
    $company = Company::query()->firstOrFail();

    $deliveryNote = $this->service->show($deliveryNote);

    $html = view('pdf.delivery-note', [
        'company' => $company,
        'deliveryNote' => $deliveryNote,
        'locale' => app()->getLocale(),
    ])->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'dejavusans',
        'margin_top' => 8,
        'margin_bottom' => 18,
        'margin_left' => 8,
        'margin_right' => 8,
    ]);

    if (app()->getLocale() === 'ar') {
        $mpdf->SetDirectionality('rtl');
    }

    $this->applySystemFooter($mpdf);

    $mpdf->WriteHTML($html);

    return response($mpdf->Output('delivery-note.pdf', 'S'))
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="delivery-note.pdf"');
}

private function applySystemFooter(Mpdf $mpdf): void
{
    $footerImage = app()->getLocale() === 'ar'
        ? public_path('images/reports/system-footer-ar.png')
        : public_path('images/reports/system-footer-en.png');

    $mpdf->SetHTMLFooter('
        <div style="text-align:center; padding-top:4px;">
            <img src="' . $footerImage . '" style="width:100%; max-width:650px;">
        </div>
    ');
}
}