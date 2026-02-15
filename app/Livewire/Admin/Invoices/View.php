<?php

namespace App\Livewire\Admin\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('View Invoice')]
class View extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['event.customer', 'items.productVariant.product']);
    }

    public function markAsPaid(): void
    {
        if ($this->invoice->status === InvoiceStatus::Paid) {
            session()->flash('error', 'Invoice is already marked as paid.');

            return;
        }

        $this->invoice->markAsPaid();

        session()->flash('success', 'Invoice marked as paid successfully.');

        $this->redirect(route('admin.invoices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.invoices.view');
    }
}
