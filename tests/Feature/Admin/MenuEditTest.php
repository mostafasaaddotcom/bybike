<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Livewire\Livewire;

test('admin can view menu edit page', function () {
    $admin = User::factory()->admin()->create();
    $menu = Menu::factory()->create();

    $this->actingAs($admin)
        ->get("/admin/menus/{$menu->id}/edit")
        ->assertOk()
        ->assertSeeLivewire(\App\Livewire\Admin\Menus\Edit::class);
});

test('non-admin cannot access menu edit page', function () {
    $user = User::factory()->create();
    $menu = Menu::factory()->create();

    $this->actingAs($user)
        ->get("/admin/menus/{$menu->id}/edit")
        ->assertForbidden();
});

test('admin can update a menu', function () {
    $admin = User::factory()->admin()->create();
    $menu = Menu::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Edit::class, ['menu' => $menu])
        ->set('name', 'New Name')
        ->set('slug', 'new-name')
        ->set('description', 'Updated description')
        ->set('sort_order', 5)
        ->set('is_available', false)
        ->call('update')
        ->assertRedirect('/admin/menus');

    $menu->refresh();
    expect($menu->name)->toBe('New Name');
    expect($menu->slug)->toBe('new-name');
    expect($menu->description)->toBe('Updated description');
    expect($menu->sort_order)->toBe(5);
    expect($menu->is_available)->toBeFalse();
});

test('admin can attach variants to menu', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $menu = Menu::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variants = ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Edit::class, ['menu' => $menu])
        ->set('selectedVariants', $variants->pluck('id')->map(fn ($id) => (string) $id)->toArray())
        ->call('update')
        ->assertRedirect('/admin/menus');

    $menu->refresh();
    expect($menu->variants()->count())->toBe(2);
});

test('admin can detach variants from menu', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $menu = Menu::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variants = ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);
    $menu->variants()->attach($variants->pluck('id'));

    expect($menu->variants()->count())->toBe(2);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Edit::class, ['menu' => $menu])
        ->set('selectedVariants', [])
        ->call('update')
        ->assertRedirect('/admin/menus');

    $menu->refresh();
    expect($menu->variants()->count())->toBe(0);
});

test('edit form pre-selects existing variants', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $menu = Menu::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variants = ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);
    $menu->variants()->attach($variants->pluck('id'));

    $component = Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Edit::class, ['menu' => $menu]);

    $selectedVariants = $component->get('selectedVariants');
    expect(count($selectedVariants))->toBe(2);
});

test('menu slug must be unique excluding self', function () {
    $admin = User::factory()->admin()->create();
    Menu::factory()->create(['slug' => 'taken-slug']);
    $menu = Menu::factory()->create(['slug' => 'my-slug']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Edit::class, ['menu' => $menu])
        ->set('slug', 'taken-slug')
        ->call('update')
        ->assertHasErrors(['slug']);
});

test('menu can keep its own slug on update', function () {
    $admin = User::factory()->admin()->create();
    $menu = Menu::factory()->create(['slug' => 'my-slug']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\Menus\Edit::class, ['menu' => $menu])
        ->set('slug', 'my-slug')
        ->call('update')
        ->assertHasNoErrors(['slug']);
});
