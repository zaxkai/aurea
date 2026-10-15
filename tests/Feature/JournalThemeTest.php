<?php

use App\Livewire\JournalPage;
use App\Models\Journal;
use App\Models\User;
use Livewire\Livewire;

it('defaults student journal theme to default', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    expect($user->journal_theme)->toBe('default');
    expect($user->getJournalThemeDetails()['name'])->toBe('Classic Aurea');
});

it('prevents free students from choosing premium journal themes', function (string $themeKey) {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
        'is_premium' => false,
    ]);

    $this->actingAs($user)
        ->from(route('journal'))
        ->post(route('journal.theme.update'), ['theme' => $themeKey])
        ->assertRedirect(route('journal'))
        ->assertSessionHas('error');

    expect($user->fresh()->journal_theme)->toBe('default');
})->with([
    'sakura' => 'sakura',
    'natal' => 'natal',
    'hujan' => 'hujan',
]);

it('allows free students to explicitly select default theme', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
        'is_premium' => false,
    ]);

    $this->actingAs($user)
        ->from(route('journal'))
        ->post(route('journal.theme.update'), ['theme' => 'default'])
        ->assertRedirect(route('journal'))
        ->assertSessionHas('success');

    expect($user->fresh()->journal_theme)->toBe('default');
});

it('allows premium students to select any journal theme', function (string $themeKey, string $themeName) {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    $user->activatePremium();

    // 1. Update theme via POST route
    $this->actingAs($user)
        ->from(route('journal'))
        ->post(route('journal.theme.update'), ['theme' => $themeKey])
        ->assertRedirect(route('journal'))
        ->assertSessionHas('success');

    expect($user->fresh()->journal_theme)->toBe($themeKey);

    // 2. Create sample journal entry
    Journal::create([
        'user_id' => $user->id,
        'title' => 'A quiet reflection',
        'content' => 'Reflecting on a peaceful day.',
        'mood' => 'neutral',
        'summary' => 'Peaceful reflection.',
        'advice' => 'Keep nurturing peace.',
        'journal_date' => today(),
    ]);

    // 3. Visit journal page and assert theme elements are rendered
    $response = $this->actingAs($user)->get(route('journal'));
    $response->assertOk();

    if ($themeKey === 'default') {
        $response->assertSee(asset('images/journal/maskot-journal3.png'), false);
    } else {
        $response->assertSee(asset("images/journal/themes/{$themeKey}/mascot-badge.jpg"), false);
    }
})->with([
    'sakura' => ['sakura', 'Cherry Blossom'],
    'natal' => ['natal', 'Christmas Holiday'],
    'hujan' => ['hujan', 'Rainy Season'],
    'default' => ['default', 'Classic Aurea'],
]);

it('allows theme selection via Livewire component', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    $user->activatePremium();

    Livewire::actingAs($user)
        ->test(JournalPage::class)
        ->call('openThemeModal')
        ->assertSet('showThemeModal', true)
        ->call('selectTheme', 'sakura')
        ->assertSet('showThemeModal', false)
        ->assertSee('Journal theme changed to Cherry Blossom successfully!');

    expect($user->fresh()->journal_theme)->toBe('sakura');
});

it('redirects free student to premium page when selecting pro theme via Livewire', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
        'is_premium' => false,
    ]);

    Livewire::actingAs($user)
        ->test(JournalPage::class)
        ->call('selectTheme', 'sakura')
        ->assertRedirect(route('premium'));

    expect($user->fresh()->journal_theme)->toBe('default');
});
