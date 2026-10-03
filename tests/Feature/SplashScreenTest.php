<?php

use App\Models\User;

it('renders the Aurea logo and sponsor sequence for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('images/logo_aurea.png')
        ->assertSee('Supported by')
        ->assertSee('images/splash/1. LOGO JHIC 2.0 1.png')
        ->assertSee('3000')
        ->assertSee('data-destination="'.route('register').'"', false);
});

it('sends signed-in users with incomplete onboarding to profile setup', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('data-destination="'.route('profile-setup').'"', false);
});

it('sends signed-in users with completed onboarding to the dashboard', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('data-destination="'.route('dashboard').'"', false);
});
