<?php

use App\Models\User;

test('authenticated user can view admin users list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/users');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Index')
        ->has('users')
    );
});

test('guest cannot view admin users list', function () {
    $response = $this->get('/admin/users');

    $response->assertRedirect('/login');
});

test('authenticated user can create a new admin account', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'New Admin User',
        'email' => 'newadmin@grcx.org',
        'password' => 'secret12345',
        'password_confirmation' => 'secret12345',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'name' => 'New Admin User',
        'email' => 'newadmin@grcx.org',
    ]);
});

test('authenticated user can update an admin account', function () {
    $admin = User::factory()->create();
    $targetUser = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->put("/admin/users/{$targetUser->id}", [
        'name' => 'Updated Admin Name',
        'email' => $targetUser->email,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'Updated Admin Name',
    ]);
});

test('authenticated user can delete another admin account', function () {
    $admin = User::factory()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/users/{$targetUser->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('users', [
        'id' => $targetUser->id,
    ]);
});

test('authenticated user cannot self delete their own account', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});
