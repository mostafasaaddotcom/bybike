<?php

namespace App\Livewire\Admin\Events;

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Customer;
use App\Models\Event;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Event $event;

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
     * Mount the component with existing event data.
     */
    public function mount(Event $event): void
    {
        $this->event = $event->load('customer');
        $this->customer_id = $event->customer_id;
        $this->brand = $event->brand->value;
        $this->type = $event->type->value;
        $this->number_of_attendees = $event->number_of_attendees;
        $this->location = $event->location;
        $this->date = $event->date->format('Y-m-d');
        $this->is_indoor = $event->is_indoor;
        $this->status = $event->status->value;
        $this->notes = $event->notes ?? '';
    }

    /**
     * Update the event.
     */
    public function update(): void
    {
        $minAttendees = $this->brand === Brand::ByBike->value ? 30 : 1;

        $validated = $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'brand' => ['required', Rule::enum(Brand::class)],
            'type' => ['required', Rule::enum(EventType::class)],
            'number_of_attendees' => ['required', 'integer', "min:{$minAttendees}"],
            'location' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'is_indoor' => ['required', 'boolean'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'notes' => ['nullable', 'string'],
        ]);

        $this->event->update($validated);

        session()->flash('success', 'Event updated successfully.');

        $this->redirect('/admin/events', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $customers = Customer::orderBy('name')->get();

        return view('livewire.admin.events.edit', [
            'customers' => $customers,
            'brands' => Brand::cases(),
            'eventTypes' => EventType::cases(),
            'eventStatuses' => EventStatus::cases(),
        ]);
    }
}
