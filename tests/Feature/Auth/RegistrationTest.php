<?php

namespace Tests\Feature\Auth;

use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.register');
});

test('new users can register', function () {
    $component = Volt::test('pages.auth.register')
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('email', 'test@example.com')
        ->set('password', 'password');

    $component->call('register');

    $component->assertRedirect(route('profile-setup', absolute: false));

    $this->assertAuthenticated();
});

test('teacher can register and is redirected to dashboard', function () {
    $component = Volt::test('pages.auth.register')
        ->set('first_name', 'Julian')
        ->set('last_name', 'Casablancas')
        ->set('role', 'teacher')
        ->set('email', 'julian_teacher_test@example.com')
        ->set('password', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $user = auth()->user();
    expect($user->role)->toBe('teacher')
        ->and($user->onboarding_completed_at)->not->toBeNull();
});
