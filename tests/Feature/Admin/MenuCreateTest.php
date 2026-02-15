<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Livewire\Livewire;

test('admin can view menu create page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/menus/create')
        ->assertOk()
        ->assertSeeLivewire(\App\Livewire\Admin\Menus\Create::class);
});

test('non-admin cannot access menu create page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/menus/create')
        ->assertForbidden();
});

test('admin can create a menu', function () {
    $admin = User::factory()->admin()->create();

    expect(Menu::count())->toBe(0);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Create::class)
        ->set('name', 'Birthday Menu')
        ->set('slug', 'birthday-menu')
        ->set('description', 'A great birthday menu')
        ->set('sort_order', 1)
        ->set('is_available', true)
        ->call('create')
        ->assertRedirect('/admin/menus');

    expect(Menu::count())->toBe(1);
    expect(Menu::first()->name)->toBe('Birthday Menu');
    expect(Menu::first()->slug)->toBe('birthday-menu');
});

test('admin can create a menu with variants', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variants = ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Create::class)
        ->set('name', 'Party Menu')
        ->set('slug', 'party-menu')
        ->set('sort_order', 0)
        ->set('is_available', true)
        ->set('selectedVariants', $variants->pluck('id')->map(fn ($id) => (string) $id)->toArray())
        ->call('create')
        ->assertRedirect('/admin/menus');

    $menu = Menu::first();
    expect($menu->variants()->count())->toBe(2);
});

test('slug is auto-generated from menu name', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Create::class)
        ->set('name', 'Birthday Party Menu')
        ->assertSet('slug', 'birthday-party-menu');
});

test('menu slug must be unique', function () {
    $admin = User::factory()->admin()->create();
    Menu::factory()->create(['slug' => 'existing-slug']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Create::class)
        ->set('name', 'New Menu')
        ->set('slug', 'existing-slug')
        ->set('sort_order', 0)
        ->set('is_available', true)
        ->call('create')
        ->assertHasErrors(['slug']);
});

test('menu name is required', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Create::class)
        ->set('name', '')
        ->set('slug', 'test')
        ->set('sort_order', 0)
        ->call('create')
        ->assertHasErrors(['name']);
});

test('menu creation rejects invalid variant IDs', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Create::class)
        ->set('name', 'Test Menu')
        ->set('slug', 'test-menu')
        ->set('sort_order', 0)
        ->set('is_available', true)
        ->set('selectedVariants', ['99999'])
        ->call('create')
        ->assertHasErrors(['selectedVariants.0']);
});
