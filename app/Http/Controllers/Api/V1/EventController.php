<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexEventRequest;
use App\Http\Requests\Api\V1\StoreEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Invoice;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * List events filtered by status and customer.
     */
    public function index(IndexEventRequest $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->where('customer_id', $request->validated('customer_id'))
            ->byStatus(EventStatus::from($request->validated('status')))
            ->latest('date')
            ->paginate();

        return EventResource::collection($events);
    }

    /**
     * Create a new pending event with a draft invoice.
     */
    public function store(StoreEventRequest $request): EventResource
    {
        $event = DB::transaction(function () use ($request) {
            $event = Event::create([
                ...$request->validated(),
                'status' => EventStatus::Pending,
            ]);

            $token = Event::generatePublicInvoiceToken();

            Invoice::create([
                'event_id' => $event->id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'status' => InvoiceStatus::Draft->value,
                'subtotal' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => 0,
                'issued_at' => now()->toDateString(),
            ]);

            $event->update(['public_invoice_token' => $token]);

            return $event;
        });

        return new EventResource($event->refresh());
    }
}
