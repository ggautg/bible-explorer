<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('community.feed'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the community feed', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('community.feed'));
    $response->assertOk();
});
