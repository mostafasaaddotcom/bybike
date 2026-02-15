<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    /**
     * Create a new customer.
     */
    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        Customer::create($validated);

        session()->flash('success', 'Customer created successfully.');

        $this->redirect('/admin/customers', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.admin.customers.create');
    }
}
