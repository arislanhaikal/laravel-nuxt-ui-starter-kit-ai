<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can list users', function () {
    actingAs($this->user)
        ->get('/users')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Users/Index')
            ->has('users'));
});

it('can create a user', function () {
    $newUserData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    actingAs($this->user)
        ->post('/users', $newUserData)
        ->assertRedirect('/users')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('can update a user', function () {
    $userToUpdate = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $updateData = [
        'name' => 'Jane Smith',
        'email' => 'jane.smith@example.com',
    ];

    actingAs($this->user)
        ->put("/users/{$userToUpdate->uuid}", $updateData)
        ->assertRedirect('/users')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'uuid' => $userToUpdate->uuid,
        'name' => 'Jane Smith',
        'email' => 'jane.smith@example.com',
    ]);
});

it('can update a user with new password', function () {
    $userToUpdate = User::factory()->create();

    $updateData = [
        'name' => $userToUpdate->name,
        'email' => $userToUpdate->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ];

    actingAs($this->user)
        ->put("/users/{$userToUpdate->uuid}", $updateData)
        ->assertRedirect('/users')
        ->assertSessionHas('success');
});

it('can delete a user', function () {
    $userToDelete = User::factory()->create();

    actingAs($this->user)
        ->delete("/users/{$userToDelete->uuid}")
        ->assertRedirect('/users')
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', [
        'uuid' => $userToDelete->uuid,
    ]);
});

it('validates required fields when creating a user', function () {
    actingAs($this->user)
        ->post('/users', [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

it('validates email uniqueness when creating a user', function () {
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    actingAs($this->user)
        ->post('/users', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasErrors(['email']);
});

it('validates password confirmation when creating a user', function () {
    actingAs($this->user)
        ->post('/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ])
        ->assertSessionHasErrors(['password']);
});

it('requires authentication to access users', function () {
    $this->get('/users')
        ->assertRedirect('/login');
});
