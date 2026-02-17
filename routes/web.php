<?php

use App\Livewire\Settings\ApiTokens;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/invoice/{token}', [\App\Http\Controllers\PublicInvoiceController::class, 'show'])
    ->name('invoice.public');
Route::post('/invoice/{token}/increment/{variantId}', [\App\Http\Controllers\PublicInvoiceController::class, 'incrementQuantity'])
    ->name('invoice.increment');
Route::post('/invoice/{token}/decrement/{variantId}', [\App\Http\Controllers\PublicInvoiceController::class, 'decrementQuantity'])
    ->name('invoice.decrement');

Route::get('dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::get('settings/api-tokens', ApiTokens::class)
        ->middleware('admin')
        ->name('api-tokens.index');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('products', \App\Livewire\Admin\Products\Index::class)->name('products.index');
    Route::get('products/create', \App\Livewire\Admin\Products\Create::class)->name('products.create');
    Route::get('products/{product}/edit', \App\Livewire\Admin\Products\Edit::class)->name('products.edit');

    Route::get('customers', \App\Livewire\Admin\Customers\Index::class)->name('customers.index');
    Route::get('customers/create', \App\Livewire\Admin\Customers\Create::class)->name('customers.create');
    Route::get('customers/{customer}/edit', \App\Livewire\Admin\Customers\Edit::class)->name('customers.edit');

    Route::get('events', \App\Livewire\Admin\Events\Index::class)->name('events.index');
    Route::get('events/create', \App\Livewire\Admin\Events\Create::class)->name('events.create');
    Route::get('events/{event}/edit', \App\Livewire\Admin\Events\Edit::class)->name('events.edit');

    Route::get('invoices', \App\Livewire\Admin\Invoices\Index::class)->name('invoices.index');
    Route::get('invoices/create', \App\Livewire\Admin\Invoices\Create::class)->name('invoices.create');
    Route::get('invoices/{invoice}/edit', \App\Livewire\Admin\Invoices\Edit::class)->name('invoices.edit');
    Route::get('invoices/{invoice}/view', \App\Livewire\Admin\Invoices\View::class)->name('invoices.view');

    Route::get('menus', \App\Livewire\Admin\Menus\Index::class)->name('menus.index');
    Route::get('menus/create', \App\Livewire\Admin\Menus\Create::class)->name('menus.create');
    Route::get('menus/{menu}/edit', \App\Livewire\Admin\Menus\Edit::class)->name('menus.edit');
});
