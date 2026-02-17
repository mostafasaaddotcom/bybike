<?php

declare(strict_types=1);

use App\Livewire\Admin\Events\Index as EventsIndex;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Livewire\Livewire;

test('deleting an event also hard-deletes its invoice and invoice items', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $event = Event::factory()
        ->has(Invoice::factory()->has(InvoiceItem::factory()->count(2), 'items'))
        ->create();

    $invoice = $event->invoice;

    Livewire::actingAs($user)
        ->test(EventsIndex::class)
        ->call('delete', $event->id);

    expect(Event::find($event->id))->toBeNull();
    expect(Invoice::find($invoice->id))->toBeNull();
    expect(InvoiceItem::where('invoice_id', $invoice->id)->count())->toBe(0);
});

test('deleting an event without an invoice does not error', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $event = Event::factory()->create();

    Livewire::actingAs($user)
        ->test(EventsIndex::class)
        ->call('delete', $event->id);

    expect(Event::find($event->id))->toBeNull();
});
