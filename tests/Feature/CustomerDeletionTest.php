<?php

declare(strict_types=1);

use App\Livewire\Admin\Customers\Index as CustomersIndex;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

test('customers index page does not show delete button', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(CustomersIndex::class)
        ->assertDontSeeHtml('wire:confirm');
});
