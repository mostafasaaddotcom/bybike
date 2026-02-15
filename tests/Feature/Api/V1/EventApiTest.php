<?php

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Event;
use App\Models\User;

// ── Authentication & Authorization ──────────────────────────────────

test('unauthenticated users cannot access event endpoints', function () {
    $this->getJson('/api/v1/events')->assertUnauthorized();
    $this->postJson('/api/v1/events')->assertUnauthorized();
});

test('non-admin users cannot access event endpoints', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/events?status=pending&customer_id=' . $customer->id)
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/events', [])
        ->assertForbidden();
});

// ── Index Endpoint ──────────────────────────────────────────────────

test('admin can list events filtered by status and customer_id', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $pendingEvents = Event::factory()->count(3)->create([
        'customer_id' => $customer->id,
        'status' => EventStatus::Pending->value,
    ]);

    Event::factory()->count(2)->completed()->create([
        'customer_id' => $customer->id,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?status=pending&customer_id=' . $customer->id)
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(3);
});

test('admin only sees events for the specified customer', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    $otherCustomer = Customer::factory()->create();

    Event::factory()->count(2)->create([
        'customer_id' => $customer->id,
        'status' => EventStatus::Pending->value,
    ]);

    Event::factory()->count(3)->create([
        'customer_id' => $otherCustomer->id,
        'status' => EventStatus::Pending->value,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?status=pending&customer_id=' . $customer->id)
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);

    collect($response->json('data'))->each(function ($event) use ($customer) {
        expect($event['customer_id'])->toBe($customer->id);
    });
});

test('admin gets empty collection when no events match', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?status=pending&customer_id=' . $customer->id)
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(0);
});

test('index returns validation error when status is missing', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?customer_id=' . $customer->id)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('index returns validation error when customer_id is missing', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?status=pending')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id']);
});

test('index returns validation error for invalid status value', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?status=invalid&customer_id=' . $customer->id)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('index returns validation error for non-existent customer_id', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?status=pending&customer_id=99999')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id']);
});

test('index excludes soft-deleted events', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $event = Event::factory()->create([
        'customer_id' => $customer->id,
        'status' => EventStatus::Pending->value,
    ]);
    $event->delete();

    Event::factory()->create([
        'customer_id' => $customer->id,
        'status' => EventStatus::Pending->value,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?status=pending&customer_id=' . $customer->id)
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('index response is paginated', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    Event::factory()->count(3)->create([
        'customer_id' => $customer->id,
        'status' => EventStatus::Pending->value,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/events?status=pending&customer_id=' . $customer->id)
        ->assertSuccessful();

    $response->assertJsonStructure([
        'data',
        'links',
        'meta',
    ]);
});

// ── Store Endpoint ──────────────────────────────────────────────────

test('admin can create an event with valid data', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => Brand::Bikis->value,
            'type' => EventType::Birthday->value,
            'number_of_attendees' => 15,
            'location' => 'Test Location',
            'date' => now()->addWeek()->toDateString(),
            'is_indoor' => true,
            'notes' => 'Test notes',
        ])
        ->assertSuccessful();

    expect($response->json('data.status'))->toBe(EventStatus::Pending->value);
    expect($response->json('data.customer_id'))->toBe($customer->id);
    expect($response->json('data.brand'))->toBe(Brand::Bikis->value);
    expect($response->json('data.location'))->toBe('Test Location');

    $this->assertDatabaseHas('events', [
        'customer_id' => $customer->id,
        'status' => EventStatus::Pending->value,
        'location' => 'Test Location',
    ]);
});

test('store creates associated invoice with draft status', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => Brand::Bikis->value,
            'type' => EventType::Corporate->value,
            'number_of_attendees' => 10,
            'location' => 'Invoice Test Location',
            'date' => now()->addMonth()->toDateString(),
            'is_indoor' => false,
        ])
        ->assertSuccessful();

    $eventId = $response->json('data.id');

    $this->assertDatabaseHas('invoices', [
        'event_id' => $eventId,
        'status' => InvoiceStatus::Draft->value,
        'subtotal' => '0.00',
        'total' => '0.00',
    ]);
});

test('store generates public_invoice_token', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => Brand::Bikis->value,
            'type' => EventType::Private->value,
            'number_of_attendees' => 5,
            'location' => 'Token Test Location',
            'date' => now()->addDays(3)->toDateString(),
            'is_indoor' => true,
        ])
        ->assertSuccessful();

    expect($response->json('data.public_invoice_url'))->not->toBeNull();

    $eventId = $response->json('data.id');
    $event = Event::find($eventId);
    expect($event->public_invoice_token)->not->toBeNull();
    expect($event->public_invoice_token)->toHaveLength(16);
});

test('store returns validation errors on missing required fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'customer_id',
            'brand',
            'type',
            'number_of_attendees',
            'location',
            'date',
            'is_indoor',
        ]);
});

test('store returns validation error for invalid brand enum', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => 'invalid-brand',
            'type' => EventType::Birthday->value,
            'number_of_attendees' => 10,
            'location' => 'Test',
            'date' => now()->addWeek()->toDateString(),
            'is_indoor' => true,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['brand']);
});

test('store returns validation error for invalid type enum', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => Brand::Bikis->value,
            'type' => 'invalid-type',
            'number_of_attendees' => 10,
            'location' => 'Test',
            'date' => now()->addWeek()->toDateString(),
            'is_indoor' => true,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

test('store returns validation error for past date', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => Brand::Bikis->value,
            'type' => EventType::Birthday->value,
            'number_of_attendees' => 10,
            'location' => 'Test',
            'date' => now()->subDay()->toDateString(),
            'is_indoor' => true,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['date']);
});

test('store returns validation error for non-existent customer_id', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => 99999,
            'brand' => Brand::Bikis->value,
            'type' => EventType::Birthday->value,
            'number_of_attendees' => 10,
            'location' => 'Test',
            'date' => now()->addWeek()->toDateString(),
            'is_indoor' => true,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id']);
});

test('store enforces minimum 30 attendees for ByBike brand', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => Brand::ByBike->value,
            'type' => EventType::Corporate->value,
            'number_of_attendees' => 10,
            'location' => 'Test',
            'date' => now()->addWeek()->toDateString(),
            'is_indoor' => false,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['number_of_attendees']);

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => Brand::ByBike->value,
            'type' => EventType::Corporate->value,
            'number_of_attendees' => 30,
            'location' => 'Test',
            'date' => now()->addWeek()->toDateString(),
            'is_indoor' => false,
        ])
        ->assertSuccessful();
});

test('event resource returns expected structure', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/events', [
            'customer_id' => $customer->id,
            'brand' => Brand::Bikis->value,
            'type' => EventType::Wedding->value,
            'number_of_attendees' => 20,
            'location' => 'Structure Test',
            'date' => now()->addMonth()->toDateString(),
            'is_indoor' => true,
            'notes' => 'Some notes',
        ])
        ->assertSuccessful();

    $response->assertJsonStructure([
        'data' => [
            'id',
            'customer_id',
            'brand',
            'type',
            'number_of_attendees',
            'location',
            'date',
            'is_indoor',
            'status',
            'notes',
            'public_invoice_url',
            'created_at',
            'updated_at',
        ],
    ]);
});
