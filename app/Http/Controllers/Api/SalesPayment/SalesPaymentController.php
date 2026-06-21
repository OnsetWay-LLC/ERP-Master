<?php

namespace App\Http\Controllers\Api\SalesPayment;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesPayment\StoreSalesPaymentRequest;
use App\Http\Resources\SalesPayment\SalesPaymentResource;
use App\Models\SalesPayment;
use App\Services\SalesPayment\SalesPaymentService;
use Illuminate\Http\Request;

class SalesPaymentController extends Controller
{
    public function __construct(
        private SalesPaymentService $service
    ) {}

    public function index(Request $request)
    {
        return SalesPaymentResource::collection(
            $this->service->getAll($request->all())
        );
    }

    public function store(StoreSalesPaymentRequest $request)
    {
        $payment = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Sales payment created successfully as draft.',
            'data' => new SalesPaymentResource($payment),
        ], 201);
    }

    public function show(SalesPayment $salesPayment)
    {
        return new SalesPaymentResource(
            $salesPayment->load([
                'salesInvoice',
                'customer',
                'paymentAccount',
                'receivableAccount',
                'journalEntry',
            ])
        );
    }

    public function submit(SalesPayment $salesPayment)
    {
        $payment = $this->service->submit($salesPayment);

        return response()->json([
            'message' => 'Sales payment submitted successfully.',
            'data' => new SalesPaymentResource($payment),
        ]);
    }

    public function cancel(SalesPayment $salesPayment)
    {
        $payment = $this->service->cancel($salesPayment);

        return response()->json([
            'message' => 'Sales payment cancelled successfully.',
            'data' => new SalesPaymentResource($payment),
        ]);
    }
}