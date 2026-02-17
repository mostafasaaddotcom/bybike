<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoiceItem;

test('purge-data command deletes all customers, events, invoices, and invoice items', function () {
    $customer = Customer::factory()->create();
    Event::factory()->for($customer)->has(Invoice::factory()->has(InvoiceItem::factory()->count(2), 'items'))->create();

    $this->artisan('app:purge-data')
        ->expectsOutput('Purged: 2 invoice items, 1 invoices, 1 events, 1 customers.')
        ->assertSuccessful();

    expect(Customer::withTrashed()->count())->toBe(0);
    expect(Event::count())->toBe(0);
    expect(Invoice::count())->toBe(0);
    expect(InvoiceItem::count())->toBe(0);
});

test('purge-data command also removes soft-deleted customers', function () {
    $customer = Customer::factory()->create();
    Event::factory()->for($customer)->create();
    $customer->delete();

    expect(Customer::withTrashed()->count())->toBe(1);
    expect(Event::count())->toBe(1);

    $this->artisan('app:purge-data')
        ->assertSuccessful();

    expect(Customer::withTrashed()->count())->toBe(0);
    expect(Event::count())->toBe(0);
});
