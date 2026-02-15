<?php

namespace App\Livewire\Admin\Events;

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $brandFilter = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    /**
     * Reset pagination when filters change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBrandFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Delete an event.
     */
    public function delete(int $eventId): void
    {
        $event = Event::findOrFail($eventId);
        $event->delete();

        session()->flash('success', 'Event deleted successfully.');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $events = Event::query()
            ->with(['customer', 'invoice'])
            ->search($this->search)
            ->when($this->brandFilter, fn ($query) => $query->where('brand', $this->brandFilter))
            ->when($this->typeFilter, fn ($query) => $query->where('type', $this->typeFilter))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->latest('date')
            ->paginate(15);

        return view('livewire.admin.events.index', [
            'events' => $events,
            'brands' => Brand::cases(),
            'eventTypes' => EventType::cases(),
            'eventStatuses' => EventStatus::cases(),
        ]);
    }
}
