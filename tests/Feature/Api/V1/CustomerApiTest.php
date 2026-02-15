<?php

use App\Models\Customer;
use App\Models\User;

test('unauthenticated users cannot access customer endpoints', function () {
    $this->postJson('/api/v1/customers/check-phone')->assertUnauthorized();
    $this->postJson('/api/v1/customers')->assertUnauthorized();
});

test('non-admin users cannot access customer endpoints', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/customers/check-phone', ['phone' => '1234567890'])
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/customers', [])
        ->assertForbidden();
});

test('admin can check phone and find existing customer', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create(['phone' => '1234567890']);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers/check-phone', ['phone' => '1234567890'])
        ->assertSuccessful();

    expect($response->json('data.exists'))->toBeTrue();
    expect($response->json('data.customer.id'))->toBe($customer->id);
    expect($response->json('data.customer.phone'))->toBe('1234567890');
});

test('admin can check phone and get not found for non-existing customer', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers/check-phone', ['phone' => '0000000000'])
        ->assertSuccessful();

    expect($response->json('data.exists'))->toBeFalse();
    expect($response->json('data.customer'))->toBeNull();
});

test('check phone returns validation error when phone is missing', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers/check-phone', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);
});

test('check phone does not find soft-deleted customers', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create(['phone' => '1234567890']);
    $customer->delete();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers/check-phone', ['phone' => '1234567890'])
        ->assertSuccessful();

    expect($response->json('data.exists'))->toBeFalse();
    expect($response->json('data.customer'))->toBeNull();
});

test('admin can create a customer with name and phone', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers', [
            'name' => 'John Doe',
            'phone' => '1234567890',
        ])
        ->assertSuccessful();

    expect($response->json('data.name'))->toBe('John Doe');
    expect($response->json('data.phone'))->toBe('1234567890');
    expect($response->json('data.email'))->toBeNull();

    $this->assertDatabaseHas('customers', [
        'name' => 'John Doe',
        'phone' => '1234567890',
    ]);
});

test('admin can create a customer with optional email', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers', [
            'name' => 'Jane Doe',
            'phone' => '0987654321',
            'email' => 'jane@example.com',
        ])
        ->assertSuccessful();

    expect($response->json('data.name'))->toBe('Jane Doe');
    expect($response->json('data.phone'))->toBe('0987654321');
    expect($response->json('data.email'))->toBe('jane@example.com');

    $this->assertDatabaseHas('customers', [
        'name' => 'Jane Doe',
        'phone' => '0987654321',
        'email' => 'jane@example.com',
    ]);
});

test('admin cannot create customer with empty required fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers', [
            'name' => '',
            'phone' => '',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'phone']);
});

test('admin cannot create customer with duplicate phone', function () {
    $admin = User::factory()->admin()->create();
    Customer::factory()->create(['phone' => '1234567890']);

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers', [
            'name' => 'New Customer',
            'phone' => '1234567890',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);
});

test('admin cannot create customer with invalid email', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers', [
            'name' => 'Test Customer',
            'phone' => '1234567890',
            'email' => 'invalid-email',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

// ── Show Endpoint ───────────────────────────────────────────────────

test('admin can get customer by id', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create([
        'name' => 'Show Customer',
        'phone' => '5551234567',
        'email' => 'show@example.com',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/customers/' . $customer->id)
        ->assertSuccessful();

    expect($response->json('data.id'))->toBe($customer->id);
    expect($response->json('data.name'))->toBe('Show Customer');
    expect($response->json('data.phone'))->toBe('5551234567');
    expect($response->json('data.email'))->toBe('show@example.com');
});

test('show returns 404 for non-existent customer', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/customers/99999')
        ->assertNotFound();
});

test('show returns 404 for soft-deleted customer', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();
    $customer->delete();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/customers/' . $customer->id)
        ->assertNotFound();
});

test('unauthenticated user cannot get customer by id', function () {
    $customer = Customer::factory()->create();

    $this->getJson('/api/v1/customers/' . $customer->id)
        ->assertUnauthorized();
});

test('non-admin user cannot get customer by id', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/customers/' . $customer->id)
        ->assertForbidden();
});

test('customer resource returns expected structure', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create([
        'name' => 'Test Customer',
        'phone' => '1234567890',
        'email' => 'test@example.com',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/customers/check-phone', ['phone' => '1234567890'])
        ->assertSuccessful();

    $response->assertJsonStructure([
        'data' => [
            'exists',
            'customer' => [
                'id',
                'name',
                'phone',
                'email',
                'created_at',
                'updated_at',
            ],
        ],
    ]);
});
