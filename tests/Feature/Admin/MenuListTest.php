<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Livewire\Livewire;

test('admin can view menus list', function () {
    $admin = User::factory()->admin()->create();
    Menu::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get('/admin/menus')
        ->assertOk();
});

test('non-admin cannot access menus list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/menus')
        ->assertForbidden();
});

test('guest cannot access menus list', function () {
    $this->get('/admin/menus')
        ->assertRedirect('/login');
});

test('menus list shows menus', function () {
    $admin = User::factory()->admin()->create();
    $menu = Menu::factory()->create([
        'name' => 'Birthday Menu',
        'is_available' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Index::class)
        ->assertSee('Birthday Menu');
});

test('admin can search menus', function () {
    $admin = User::factory()->admin()->create();
    Menu::factory()->create(['name' => 'Birthday Menu']);
    Menu::factory()->create(['name' => 'Wedding Menu']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Index::class)
        ->set('search', 'Birthday')
        ->assertSee('Birthday Menu')
        ->assertDontSee('Wedding Menu');
});

test('admin can filter menus by availability', function () {
    $admin = User::factory()->admin()->create();
    Menu::factory()->create(['name' => 'Available Menu', 'is_available' => true]);
    Menu::factory()->create(['name' => 'Unavailable Menu', 'is_available' => false]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Index::class)
        ->set('availabilityFilter', 'available')
        ->assertSee('Available Menu')
        ->assertDontSee('Unavailable Menu');
});

test('admin can delete menu', function () {
    $admin = User::factory()->admin()->create();
    $menu = Menu::factory()->create();

    expect(Menu::count())->toBe(1);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Index::class)
        ->call('delete', $menu->id);

    expect(Menu::count())->toBe(0);
});

test('menus list shows variant count', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $menu = Menu::factory()->create(['name' => 'Test Menu']);
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variants = ProductVariant::factory()->count(3)->create(['product_id' => $product->id]);
    $menu->variants()->attach($variants->pluck('id'));

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Index::class)
        ->assertSee('Test Menu')
        ->assertSee('3');
});
