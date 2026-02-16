<?php

declare(strict_types=1);

use App\Livewire\Admin\Events\Index as EventsIndex;
use App\Livewire\Admin\Invoices\Index as InvoicesIndex;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Livewire;

test('deleting an event also soft-deletes its invoice', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $event = Event::factory()
        ->has(Invoice::factory())
        ->create();

    $invoice = $event->invoice;

    Livewire::actingAs($user)
        ->test(EventsIndex::class)
        ->call('delete', $event->id);

    expect($event->fresh()->trashed())->toBeTrue();
    expect(Invoice::find($invoice->id))->toBeNull();
    expect(Invoice::withTrashed()->find($invoice->id)->trashed())->toBeTrue();
});

test('deleting an event without an invoice does not error', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $event = Event::factory()->create();

    Livewire::actingAs($user)
        ->test(EventsIndex::class)
        ->call('delete', $event->id);

    expect(Event::find($event->id))->toBeNull();
});

test('invoices index page loads without error after event is soft-deleted', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $event = Event::factory()
        ->has(Invoice::factory())
        ->create();

    Livewire::actingAs($user)
        ->test(EventsIndex::class)
        ->call('delete', $event->id);

    Livewire::actingAs($user)
        ->test(InvoicesIndex::class)
        ->assertSuccessful();
});
