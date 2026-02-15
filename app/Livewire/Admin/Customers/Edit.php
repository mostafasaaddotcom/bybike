<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
use Livewire\Component;

class Edit extends Component
{
    public Customer $customer;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    /**
     * Mount the component with existing customer data.
     */
    public function mount(Customer $customer): void
    {
        $this->customer = $customer->load('events');
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email ?? '';
    }

    /**
     * Update the customer.
     */
    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $this->customer->update($validated);

        session()->flash('success', 'Customer updated successfully.');

        $this->redirect('/admin/customers', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.admin.customers.edit');
    }
}
