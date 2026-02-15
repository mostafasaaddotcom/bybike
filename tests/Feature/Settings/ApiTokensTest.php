<?php

use App\Livewire\Settings\ApiTokens;
use App\Models\User;
use Livewire\Livewire;

test('api tokens page is not accessible to non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/api-tokens')
        ->assertForbidden();
});

test('api tokens page is displayed for admin users', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/settings/api-tokens')
        ->assertOk();
});

test('admin can create an api token', function () {
    $admin = User::factory()->admin()->create();

    $response = Livewire::actingAs($admin)
        ->test(ApiTokens::class)
        ->set('tokenName', 'Test Token')
        ->call('createToken');

    $response
        ->assertHasNoErrors()
        ->assertDispatched('token-created');

    expect($response->get('newToken'))->not->toBeEmpty();
    expect($admin->tokens()->where('name', 'Test Token')->exists())->toBeTrue();
});

test('token name is required', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(ApiTokens::class)
        ->set('tokenName', '')
        ->call('createToken')
        ->assertHasErrors(['tokenName' => 'required']);
});

test('admin can delete an api token', function () {
    $admin = User::factory()->admin()->create();
    $token = $admin->createToken('To Delete');

    Livewire::actingAs($admin)
        ->test(ApiTokens::class)
        ->call('deleteToken', $token->accessToken->id)
        ->assertDispatched('token-deleted');

    expect($admin->tokens()->count())->toBe(0);
});
