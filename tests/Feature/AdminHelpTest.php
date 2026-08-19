<?php

use App\Models\User;

test('authenticated user can view admin help guide', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/help');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Help/Index')
    );
});

test('guest cannot view admin help guide', function () {
    $response = $this->get('/admin/help');

    $response->assertRedirect('/login');
});
