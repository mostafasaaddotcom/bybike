<?php

namespace App\Livewire\Admin\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Invoices')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(Invoice $invoice): void
    {
        $invoice->delete();

        session()->flash('success', 'Invoice deleted successfully.');
    }

    public function render()
    {
        $invoices = Invoice::query()
            ->with(['event' => fn ($q) => $q->withTrashed(), 'event.customer' => fn ($q) => $q->withTrashed()])
            ->search($this->search)
            ->when($this->statusFilter, fn ($q) => $q->byStatus(InvoiceStatus::from($this->statusFilter)))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.invoices.index', [
            'invoices' => $invoices,
        ]);
    }
}
