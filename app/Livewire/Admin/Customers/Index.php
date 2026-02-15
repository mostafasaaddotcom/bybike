<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Delete a customer.
     */
    public function delete(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);
        $customer->delete();

        session()->flash('success', 'Customer deleted successfully.');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $customers = Customer::query()
            ->search($this->search)
            ->withCount('events')
            ->latest()
            ->paginate(15);

        return view('livewire.admin.customers.index', [
            'customers' => $customers,
        ]);
    }
}
