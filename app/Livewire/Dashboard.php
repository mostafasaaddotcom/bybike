<?php

namespace App\Livewire;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.dashboard', [
            'totalCustomers' => Customer::count(),
            'draftInvoices' => Invoice::where('status', InvoiceStatus::Draft)->count(),
            'pendingInvoices' => Invoice::where('status', InvoiceStatus::Pending)->count(),
            'paidInvoices' => Invoice::where('status', InvoiceStatus::Paid)->count(),
            'totalInvoices' => Invoice::count(),
        ]);
    }
}
