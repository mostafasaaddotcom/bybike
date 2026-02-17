<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateInvoiceStatusRequest;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    /**
     * Update the status of the specified invoice.
     */
    public function updateStatus(UpdateInvoiceStatusRequest $request, Invoice $invoice): JsonResponse
    {
        $status = InvoiceStatus::from($request->validated('status'));

        $invoice->status = $status;

        if ($status === InvoiceStatus::Paid) {
            $invoice->paid_at = now();
        }

        $invoice->save();

        return response()->json([
            'message' => 'Invoice status updated successfully.',
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status->value,
                'paid_at' => $invoice->paid_at?->toISOString(),
            ],
        ]);
    }
}
