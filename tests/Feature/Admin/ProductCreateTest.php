<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Livewire\Livewire;

test('admin can view product create page', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->create();

    $this->actingAs($admin)
        ->get('/admin/products/create')
        ->assertOk()
        ->assertSeeLivewire(\App\Livewire\Admin\Products\Create::class);
});

test('non-admin cannot access product create page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/products/create')
        ->assertForbidden();
});

test('admin can create a product with variants', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    expect(Product::count())->toBe(0);
    expect(ProductVariant::count())->toBe(0);

    $component = Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Create::class)
        ->set('category_id', $category->id)
        ->set('name', 'BBQ Burger')
        ->set('slug', 'bbq-burger')
        ->set('description', 'Delicious BBQ burger')
        ->set('sort_order', 1)
        ->set('is_available', true)
        ->set('variants.0.name', 'Regular')
        ->set('variants.0.minimum_order_quantity', 5)
        ->set('variants.0.increase_rate', 5)
        ->set('variants.0.is_available', true)
        ->set('variants.0.price_tiers.0.quantity_from', 1)
        ->set('variants.0.price_tiers.0.quantity_to', 10)
        ->set('variants.0.price_tiers.0.price', 10.50)
        ->call('create')
        ->assertRedirect('/admin/products');

    expect(Product::count())->toBe(1);
    expect(Product::first()->name)->toBe('BBQ Burger');
    expect(ProductVariant::count())->toBe(1);
    expect(ProductVariant::first()->name)->toBe('Regular');
    expect(ProductVariant::first()->priceTiers()->count())->toBe(1);
});

test('slug is auto-generated from product name', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Create::class)
        ->set('name', 'BBQ Burger Special')
        ->assertSet('slug', 'bbq-burger-special');
});

test('product creation requires at least one variant', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Create::class)
        ->set('category_id', $category->id)
        ->set('name', 'Test Product')
        ->set('slug', 'test-product')
        ->set('variants', [])
        ->call('create')
        ->assertHasErrors(['variants']);
});

test('product creation requires valid variant data', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Create::class)
        ->set('variants.0.name', '')
        ->set('variants.0.minimum_order_quantity', 0)
        ->call('create')
        ->assertHasErrors(['variants.0.name', 'variants.0.minimum_order_quantity']);
});

test('variant requires at least one price tier', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Create::class)
        ->set('category_id', $category->id)
        ->set('name', 'Test Product')
        ->set('slug', 'test-product')
        ->set('variants.0.name', 'Regular')
        ->set('variants.0.price_tiers', [])
        ->call('create')
        ->assertHasErrors(['variants.0.price_tiers']);
});

test('product slug must be unique', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'slug' => 'existing-slug',
    ]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Create::class)
        ->set('category_id', $category->id)
        ->set('name', 'New Product')
        ->set('slug', 'existing-slug')
        ->set('sort_order', 0)
        ->set('is_available', true)
        ->call('create')
        ->assertHasErrors(['slug']);
});
