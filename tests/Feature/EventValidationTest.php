<?php

declare(strict_types=1);

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Livewire\Admin\Events\Create;
use App\Livewire\Admin\Events\Edit;
use App\Models\Customer;
use App\Models\Event;

test('brand field is required when creating event', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('customer_id', $customer->id)
        ->set('brand', '')
        ->set('type', EventType::Birthday->value)
        ->set('number_of_attendees', 50)
        ->set('location', 'Test Location')
        ->set('date', now()->addDays(7)->format('Y-m-d'))
        ->set('is_indoor', true)
        ->set('status', EventStatus::Pending->value)
        ->call('create')
        ->assertHasErrors(['brand']);
});

test('byBike brand requires minimum 30 attendees when creating event', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('customer_id', $customer->id)
        ->set('brand', Brand::ByBike->value)
        ->set('type', EventType::Birthday->value)
        ->set('number_of_attendees', 29)
        ->set('location', 'Test Location')
        ->set('date', now()->addDays(7)->format('Y-m-d'))
        ->set('is_indoor', true)
        ->set('status', EventStatus::Pending->value)
        ->call('create')
        ->assertHasErrors(['number_of_attendees']);
});

test('byBike brand accepts 30 or more attendees when creating event', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('customer_id', $customer->id)
        ->set('brand', Brand::ByBike->value)
        ->set('type', EventType::Birthday->value)
        ->set('number_of_attendees', 30)
        ->set('location', 'Test Location')
        ->set('date', now()->addDays(7)->format('Y-m-d'))
        ->set('is_indoor', true)
        ->set('status', EventStatus::Pending->value)
        ->call('create')
        ->assertHasNoErrors();

    expect(Event::where('brand', Brand::ByBike->value)->exists())->toBeTrue();
});

test('Bikis brand accepts minimum 1 attendee when creating event', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('customer_id', $customer->id)
        ->set('brand', Brand::Bikis->value)
        ->set('type', EventType::Birthday->value)
        ->set('number_of_attendees', 1)
        ->set('location', 'Test Location')
        ->set('date', now()->addDays(7)->format('Y-m-d'))
        ->set('is_indoor', true)
        ->set('status', EventStatus::Pending->value)
        ->call('create')
        ->assertHasNoErrors();

    expect(Event::where('brand', Brand::Bikis->value)->exists())->toBeTrue();
});

test('Bikis brand accepts any number of attendees when creating event', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('customer_id', $customer->id)
        ->set('brand', Brand::Bikis->value)
        ->set('type', EventType::Birthday->value)
        ->set('number_of_attendees', 15)
        ->set('location', 'Test Location')
        ->set('date', now()->addDays(7)->format('Y-m-d'))
        ->set('is_indoor', true)
        ->set('status', EventStatus::Pending->value)
        ->call('create')
        ->assertHasNoErrors();

    expect(Event::where('brand', Brand::Bikis->value)->where('number_of_attendees', 15)->exists())->toBeTrue();
});

test('byBike brand requires minimum 30 attendees when updating event', function () {
    $customer = Customer::factory()->create();
    $event = Event::factory()->create([
        'customer_id' => $customer->id,
        'brand' => Brand::ByBike->value,
        'number_of_attendees' => 50,
    ]);

    Livewire::test(Edit::class, ['event' => $event])
        ->set('brand', Brand::ByBike->value)
        ->set('number_of_attendees', 29)
        ->call('update')
        ->assertHasErrors(['number_of_attendees']);
});

test('Bikis brand accepts minimum 1 attendee when updating event', function () {
    $customer = Customer::factory()->create();
    $event = Event::factory()->create([
        'customer_id' => $customer->id,
        'brand' => Brand::Bikis->value,
        'number_of_attendees' => 50,
    ]);

    Livewire::test(Edit::class, ['event' => $event])
        ->set('brand', Brand::Bikis->value)
        ->set('number_of_attendees', 1)
        ->call('update')
        ->assertHasNoErrors();

    expect($event->fresh()->number_of_attendees)->toBe(1);
});
