<?php

use App\Models\User;

test('guests cannot access pulse dashboard', function () {
    $response = $this->get('/pulse');

    if ($response->isRedirection()) {
        $response->assertRedirect(route('login'));
    } else {
        $response->assertForbidden();
    }
});

test('authenticated admin users can access pulse dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/pulse');

    $response->assertOk();
});
