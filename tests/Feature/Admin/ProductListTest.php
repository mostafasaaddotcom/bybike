<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('admin can view products list', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->create(['name' => 'American']);
    Product::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertOk();
});

test('non-admin cannot access products list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/products')
        ->assertForbidden();
});

test('guest cannot access products list', function () {
    $this->get('/admin/products')
        ->assertRedirect('/login');
});

test('products list shows products with filters', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'BBQ']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'BBQ Burger',
        'is_available' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Index::class)
        ->assertSee('BBQ Burger')
        ->assertSee('BBQ');
});

test('admin can search products', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id, 'name' => 'Burger']);
    Product::factory()->create(['category_id' => $category->id, 'name' => 'Pizza']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Index::class)
        ->set('search', 'Burger')
        ->assertSee('Burger')
        ->assertDontSee('Pizza');
});

test('admin can filter products by category', function () {
    $admin = User::factory()->admin()->create();
    $bbq = Category::factory()->create(['name' => 'BBQ']);
    $mexican = Category::factory()->create(['name' => 'Mexican']);

    Product::factory()->create(['category_id' => $bbq->id, 'name' => 'BBQ Ribs']);
    Product::factory()->create(['category_id' => $mexican->id, 'name' => 'Tacos']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Index::class)
        ->set('categoryFilter', $bbq->id)
        ->assertSee('BBQ Ribs')
        ->assertDontSee('Tacos');
});

test('admin can delete product', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    expect(Product::count())->toBe(1);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Products\Index::class)
        ->call('delete', $product->id);

    expect(Product::count())->toBe(0);
});
