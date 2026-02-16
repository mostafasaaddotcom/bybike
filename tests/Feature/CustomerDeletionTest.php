<?php

declare(strict_types=1);

use App\Livewire\Admin\Customers\Index as CustomersIndex;
use App\Livewire\Admin\Events\Index as EventsIndex;
use App\Livewire\Admin\Invoices\Index as InvoicesIndex;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Livewire;

test('deleting a customer soft-deletes their events and invoices', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $customer = Customer::factory()->create();
    $event = Event::factory()
        ->for($customer)
        ->has(Invoice::factory())
        ->create();

    $invoice = $event->invoice;

    Livewire::actingAs($user)
        ->test(CustomersIndex::class)
        ->call('delete', $customer->id);

    expect($customer->fresh()->trashed())->toBeTrue();
    expect($event->fresh()->trashed())->toBeTrue();
    expect(Invoice::find($invoice->id))->toBeNull();
    expect(Invoice::withTrashed()->find($invoice->id)->trashed())->toBeTrue();
});

test('deleting a customer without events does not error', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(CustomersIndex::class)
        ->call('delete', $customer->id);

    expect($customer->fresh()->trashed())->toBeTrue();
});

test('events index page loads without error after customer is soft-deleted', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $customer = Customer::factory()->create();
    Event::factory()->for($customer)->has(Invoice::factory())->create();

    Livewire::actingAs($user)
        ->test(CustomersIndex::class)
        ->call('delete', $customer->id);

    Livewire::actingAs($user)
        ->test(EventsIndex::class)
        ->assertSuccessful();
});

test('invoices index page loads without error after customer is soft-deleted', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $customer = Customer::factory()->create();
    Event::factory()->for($customer)->has(Invoice::factory())->create();

    Livewire::actingAs($user)
        ->test(CustomersIndex::class)
        ->call('delete', $customer->id);

    Livewire::actingAs($user)
        ->test(InvoicesIndex::class)
        ->assertSuccessful();
});
