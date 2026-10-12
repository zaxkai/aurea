<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/profile');

    $response
        ->assertOk()
        ->assertSeeVolt('profile-page');
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('profile-page')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('saveProfile');

    $component
        ->assertHasNoErrors();

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
});

test('teacher profile displays teacher role and teaching overview', function () {
    $teacher = User::factory()->create([
        'name' => 'Julian Casablancas',
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher);

    $response = $this->get('/profile');

    $response
        ->assertOk()
        ->assertSee('Teacher')
        ->assertSee('Teaching Overview')
        ->assertSee('Classrooms Managed');
});
