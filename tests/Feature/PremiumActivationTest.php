<?php

use App\Livewire\Settings;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests to sign in before opening the premium page', function () {
    $this->get(route('premium'))
        ->assertRedirect(route('login'));
});

it('does not expose legacy payment checkout in demo mode', function () {
    $this->actingAs(User::factory()->create())
        ->post('/subscription/checkout')
        ->assertNotFound();
});

it('activates premium for the signed-in user in demo mode', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('premium.activate'))
        ->assertRedirect(route('premium'))
        ->assertSessionHas('success');

    $user->refresh();

    expect($user->isPremium())->toBeTrue();
    expect($user->premium_started_at)->not->toBeNull();
    expect($user->premium_expires_at)->toBeNull();

    $this->get(route('premium'))
        ->assertOk()
        ->assertSee('Premium Aktif')
        ->assertDontSee('Aktifkan Premium');
});

it('returns the signed-in user to free from settings', function () {
    $user = User::factory()->create();
    $user->activatePremium();

    $this->actingAs($user);

    Livewire::test(Settings::class)
        ->call('deactivatePremium')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->isPremium())->toBeFalse();
    expect($user->premium_started_at)->toBeNull();
    expect($user->premium_expires_at)->toBeNull();
});
