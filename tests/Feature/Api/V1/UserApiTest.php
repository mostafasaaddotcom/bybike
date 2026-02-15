<?php

use App\Models\User;

test('unauthenticated users cannot access user endpoints', function () {
    $this->getJson('/api/v1/users')->assertUnauthorized();
    $this->postJson('/api/v1/users')->assertUnauthorized();
    $this->getJson('/api/v1/users/1')->assertUnauthorized();
    $this->putJson('/api/v1/users/1')->assertUnauthorized();
});

test('non-admin users cannot access user endpoints', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/users')
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/users', [])
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/users/' . $user->id)
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/users/' . $user->id, [])
        ->assertForbidden();
});

test('admin can list all users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/users')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(4);
});

test('admin can create a user with valid data', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ])
        ->assertSuccessful();

    expect($response->json('data.name'))->toBe('John Doe');
    expect($response->json('data.email'))->toBe('john@example.com');
    expect($response->json('data.role'))->toBe('customer');

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

test('admin cannot create user with invalid data', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/users', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('admin cannot create user with duplicate email', function () {
    $admin = User::factory()->admin()->create();
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/users', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('admin can view a specific user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/users/' . $user->id)
        ->assertSuccessful();

    expect($response->json('data.name'))->toBe('Jane Doe');
    expect($response->json('data.email'))->toBe('jane@example.com');
});

test('admin can update a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/users/' . $user->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ])
        ->assertSuccessful();

    expect($response->json('data.name'))->toBe('Updated Name');
    expect($response->json('data.email'))->toBe('updated@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

test('admin cannot update user with invalid data', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/users/' . $user->id, [
            'email' => 'invalid-email',
            'password' => 'short',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('admin cannot update user with duplicate email', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/users/' . $user1->id, [
            'email' => 'user2@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('admin can update user password', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $oldPasswordHash = $user->password;

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/users/' . $user->id, [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertSuccessful();

    $user->refresh();
    expect($user->password)->not->toBe($oldPasswordHash);
});

test('user resource returns expected structure', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'customer',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/users/' . $user->id)
        ->assertSuccessful();

    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'email',
            'role',
            'email_verified_at',
            'created_at',
            'updated_at',
        ],
    ]);
});
