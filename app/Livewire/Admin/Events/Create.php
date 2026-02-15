<?php

namespace App\Livewire\Admin\Events;

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public ?int $customer_id = null;

    public string $brand = '';

    public string $type = '';

    public int $number_of_attendees = 10;

    public string $location = '';

    public string $date = '';

    public bool $is_indoor = false;

    public string $status = 'pending';

    public string $notes = '';

    /**
     * Create a new event.
     */
    public function create(): void
    {
        $minAttendees = $this->brand === Brand::ByBike->value ? 30 : 1;

        $validated = $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'brand' => ['required', Rule::enum(Brand::class)],
            'type' => ['required', Rule::enum(EventType::class)],
            'number_of_attendees' => ['required', 'integer', "min:{$minAttendees}"],
            'location' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'is_indoor' => ['required', 'boolean'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $event = Event::create($validated);

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
        });

        session()->flash('success', 'Event created successfully.');

        $this->redirect('/admin/events', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $customers = Customer::orderBy('name')->get();

        return view('livewire.admin.events.create', [
            'customers' => $customers,
            'brands' => Brand::cases(),
            'eventTypes' => EventType::cases(),
            'eventStatuses' => EventStatus::cases(),
        ]);
    }
}
