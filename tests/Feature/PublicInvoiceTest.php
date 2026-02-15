<?php

declare(strict_types=1);

use App\Enums\Brand;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\InvoiceStatus;
use App\Livewire\Admin\Events\Create;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductVariant;

test('creating event auto-creates invoice with draft status', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('customer_id', $customer->id)
        ->set('brand', Brand::ByBike->value)
        ->set('type', EventType::Birthday->value)
        ->set('number_of_attendees', 50)
        ->set('location', 'Test Location')
        ->set('date', now()->addDays(7)->format('Y-m-d'))
        ->set('is_indoor', true)
        ->set('status', EventStatus::Pending->value)
        ->call('create');

    $event = Event::latest()->first();

    expect($event)->not->toBeNull();
    expect($event->invoice)->not->toBeNull();
    expect($event->invoice->status)->toBe(InvoiceStatus::Draft);
});

test('creating event generates unique public invoice token', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('customer_id', $customer->id)
        ->set('brand', Brand::Bikis->value)
        ->set('type', EventType::Wedding->value)
        ->set('number_of_attendees', 100)
        ->set('location', 'Grand Hall')
        ->set('date', now()->addDays(30)->format('Y-m-d'))
        ->set('is_indoor', false)
        ->set('status', EventStatus::Pending->value)
        ->call('create');

    $event = Event::latest()->first();

    expect($event->public_invoice_token)->not->toBeNull();
    expect(strlen($event->public_invoice_token))->toBe(16);
});

test('public invoice token is unique across events', function () {
    $customer = Customer::factory()->create();

    Event::factory()->count(3)->create([
        'customer_id' => $customer->id,
        'public_invoice_token' => null,
    ]);

    $events = Event::all();

    foreach ($events as $event) {
        $token = Event::generatePublicInvoiceToken();
        $event->update(['public_invoice_token' => $token]);
    }

    $tokens = Event::pluck('public_invoice_token')->toArray();
    $uniqueTokens = array_unique($tokens);

    expect(count($tokens))->toBe(count($uniqueTokens));
});

test('public invoice route is accessible without authentication', function () {
    $event = Event::factory()
        ->has(Invoice::factory())
        ->create(['public_invoice_token' => Event::generatePublicInvoiceToken()]);

    $response = $this->get("/invoice/{$event->public_invoice_token}");

    $response->assertSuccessful();
    $response->assertSee($event->invoice->invoice_number);
});

test('public invoice route returns 404 for invalid token', function () {
    $response = $this->get('/invoice/invalid-token-that-does-not-exist');

    $response->assertNotFound();
});

test('public invoice page displays event and customer details', function () {
    $customer = Customer::factory()->create(['name' => 'John Doe']);
    $event = Event::factory()
        ->for($customer)
        ->has(Invoice::factory())
        ->create([
            'public_invoice_token' => Event::generatePublicInvoiceToken(),
            'location' => 'Paradise Hall',
        ]);

    $response = $this->get("/invoice/{$event->public_invoice_token}");

    $response->assertSuccessful()
        ->assertSee('John Doe')
        ->assertSee('Paradise Hall')
        ->assertSee($event->invoice->invoice_number);
});

test('client can add item to invoice through public link', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->hasVariants(1)->create();
    $variant = $product->variants->first();

    // Set variant properties
    $variant->update([
        'minimum_order_quantity' => 5,
        'increase_rate' => 1,
        'is_available' => true,
    ]);

    // Create price tier for the variant
    $variant->priceTiers()->create([
        'quantity_from' => 1,
        'quantity_to' => null,
        'price' => 50.00,
    ]);

    $event = Event::factory()
        ->for($customer)
        ->has(Invoice::factory())
        ->create(['public_invoice_token' => Event::generatePublicInvoiceToken()]);

    $response = $this->postJson("/invoice/{$event->public_invoice_token}/increment/{$variant->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'quantity' => 5,
        ]);

    $event->invoice->refresh();

    expect($event->invoice->items->count())->toBe(1);
    expect($event->invoice->items->first()->quantity)->toBe(5);
});

test('client can increment item quantity through public link', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->hasVariants(1)->create();
    $variant = $product->variants->first();

    // Set variant properties
    $variant->update([
        'minimum_order_quantity' => 1,
        'increase_rate' => 5,
        'is_available' => true,
    ]);

    // Create price tier
    $variant->priceTiers()->create([
        'quantity_from' => 1,
        'quantity_to' => null,
        'price' => 50.00,
    ]);

    $event = Event::factory()
        ->for($customer)
        ->has(Invoice::factory()->hasItems(1, ['product_variant_id' => $variant->id, 'quantity' => 10]))
        ->create(['public_invoice_token' => Event::generatePublicInvoiceToken()]);

    $response = $this->postJson("/invoice/{$event->public_invoice_token}/increment/{$variant->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'quantity' => 15,
        ]);

    $event->invoice->refresh();

    expect($event->invoice->items->first()->quantity)->toBe(15);
});

test('client can decrement item quantity through public link', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->hasVariants(1)->create();
    $variant = $product->variants->first();

    // Set variant properties
    $variant->update([
        'minimum_order_quantity' => 1,
        'increase_rate' => 5,
        'is_available' => true,
    ]);

    // Create price tier
    $variant->priceTiers()->create([
        'quantity_from' => 1,
        'quantity_to' => null,
        'price' => 50.00,
    ]);

    $event = Event::factory()
        ->for($customer)
        ->has(Invoice::factory()->hasItems(1, ['product_variant_id' => $variant->id, 'quantity' => 10]))
        ->create(['public_invoice_token' => Event::generatePublicInvoiceToken()]);

    $response = $this->postJson("/invoice/{$event->public_invoice_token}/decrement/{$variant->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'quantity' => 5,
        ]);

    $event->invoice->refresh();

    expect($event->invoice->items->first()->quantity)->toBe(5);
});

test('client can remove item by decrementing to zero', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->hasVariants(1)->create();
    $variant = $product->variants->first();

    // Set variant properties
    $variant->update([
        'minimum_order_quantity' => 5,
        'increase_rate' => 5,
        'is_available' => true,
    ]);

    // Create price tier
    $variant->priceTiers()->create([
        'quantity_from' => 1,
        'quantity_to' => null,
        'price' => 50.00,
    ]);

    $event = Event::factory()
        ->for($customer)
        ->has(Invoice::factory()->hasItems(1, ['product_variant_id' => $variant->id, 'quantity' => 5]))
        ->create(['public_invoice_token' => Event::generatePublicInvoiceToken()]);

    $response = $this->postJson("/invoice/{$event->public_invoice_token}/decrement/{$variant->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'quantity' => 0,
            'removed' => true,
        ]);

    $event->invoice->refresh();

    expect($event->invoice->items->count())->toBe(0);
});

test('invoice totals are recalculated after adding items', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->hasVariants(1)->create();
    $variant = $product->variants->first();

    // Set variant properties
    $variant->update([
        'minimum_order_quantity' => 2,
        'increase_rate' => 1,
        'is_available' => true,
    ]);

    // Create price tier for the variant
    $variant->priceTiers()->create([
        'quantity_from' => 1,
        'quantity_to' => null,
        'price' => 100.00,
    ]);

    $event = Event::factory()
        ->for($customer)
        ->has(Invoice::factory())
        ->create(['public_invoice_token' => Event::generatePublicInvoiceToken()]);

    $response = $this->postJson("/invoice/{$event->public_invoice_token}/increment/{$variant->id}");

    $response->assertSuccessful();

    $event->invoice->refresh();

    expect($event->invoice->subtotal)->toBeGreaterThan(0);
    expect($event->invoice->total)->toBeGreaterThan(0);
});

test('public invoice url accessor returns correct url', function () {
    $event = Event::factory()
        ->has(Invoice::factory())
        ->create(['public_invoice_token' => 'test-token-123']);

    $expectedUrl = route('invoice.public', ['token' => 'test-token-123']);

    expect($event->public_invoice_url)->toBe($expectedUrl);
});

test('public invoice url accessor returns null when no token', function () {
    $event = Event::factory()
        ->has(Invoice::factory())
        ->create(['public_invoice_token' => null]);

    expect($event->public_invoice_url)->toBeNull();
});

test('public invoice page displays available menus', function () {
    $event = Event::factory()
        ->has(Invoice::factory())
        ->create(['public_invoice_token' => Event::generatePublicInvoiceToken()]);

    $variant1 = ProductVariant::factory()->create(['is_available' => true]);
    $variant1->priceTiers()->create(['quantity_from' => 1, 'quantity_to' => null, 'price' => 10.00]);

    $variant2 = ProductVariant::factory()->create(['is_available' => true]);
    $variant2->priceTiers()->create(['quantity_from' => 1, 'quantity_to' => null, 'price' => 20.00]);

    $menuA = Menu::factory()->create(['name' => 'Breakfast Menu', 'is_available' => true]);
    $menuA->variants()->attach($variant1->id);

    $menuB = Menu::factory()->create(['name' => 'Lunch Menu', 'is_available' => true]);
    $menuB->variants()->attach($variant2->id);

    $response = $this->get("/invoice/{$event->public_invoice_token}");

    $response->assertSuccessful()
        ->assertSee('Breakfast Menu')
        ->assertSee('Lunch Menu');
});

test('public invoice page hides unavailable menus', function () {
    $event = Event::factory()
        ->has(Invoice::factory())
        ->create(['public_invoice_token' => Event::generatePublicInvoiceToken()]);

    $variant1 = ProductVariant::factory()->create(['is_available' => true]);
    $variant1->priceTiers()->create(['quantity_from' => 1, 'quantity_to' => null, 'price' => 10.00]);

    $variant2 = ProductVariant::factory()->create(['is_available' => true]);
    $variant2->priceTiers()->create(['quantity_from' => 1, 'quantity_to' => null, 'price' => 20.00]);

    $availableMenu = Menu::factory()->create(['name' => 'Available Menu', 'is_available' => true]);
    $availableMenu->variants()->attach($variant1->id);

    $unavailableMenu = Menu::factory()->unavailable()->create(['name' => 'Hidden Menu']);
    $unavailableMenu->variants()->attach($variant2->id);

    $response = $this->get("/invoice/{$event->public_invoice_token}");

    $response->assertSuccessful()
        ->assertSee('Available Menu')
        ->assertDontSee('Hidden Menu');
});
