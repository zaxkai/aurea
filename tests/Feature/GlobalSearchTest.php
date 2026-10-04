<?php

use App\Livewire\GlobalSearch;
use App\Livewire\JournalPage;
use App\Models\Journal;
use App\Models\User;
use Livewire\Livewire;

it('renders global search input in topbar', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSeeLivewire(GlobalSearch::class);
});

it('searches app pages by keyword or name', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(GlobalSearch::class)
        ->set('query', 'ai')
        ->assertSee('AI Aurea')
        ->assertSee('/ai-aurea')
        ->set('query', 'tree')
        ->assertSee('Habit Growth Tree')
        ->assertSee('/habit-growth-tree')
        ->set('query', 'journal')
        ->assertSee('Journal')
        ->set('query', 'dash')
        ->assertSee('Dashboard');
});

it('searches journal entries by what the user typed in content or title', function () {
    $user = User::factory()->create();

    Journal::create([
        'user_id' => $user->id,
        'title' => 'Productive Day',
        'content' => 'Hari ini saya belajar coding Laravel dan olahraga pagi.',
        'mood' => 'energetic',
        'summary' => 'Coding and morning workout.',
        'advice' => 'Keep maintaining the high energy.',
        'journal_date' => today(),
    ]);

    Journal::create([
        'user_id' => $user->id,
        'title' => 'Late Night Thoughts',
        'content' => 'Banyak tugas deadline kampus yang belum selesai bikin pusing.',
        'mood' => 'stressed',
        'summary' => 'Stressed about college deadlines.',
        'advice' => 'Take breaks.',
        'journal_date' => today(),
    ]);

    Livewire::actingAs($user)
        ->test(GlobalSearch::class)
        ->set('query', 'Laravel')
        ->assertSee('Productive Day')
        ->assertDontSee('Late Night Thoughts')
        ->set('query', 'deadline')
        ->assertSee('Late Night Thoughts')
        ->assertDontSee('Productive Day');
});

it('does not show journals from other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Journal::create([
        'user_id' => $user2->id,
        'title' => 'Secret Notes',
        'content' => 'Top secret private journal entry.',
        'mood' => 'calm',
        'summary' => 'Private secret.',
        'advice' => 'Keep it secret.',
        'journal_date' => today(),
    ]);

    Livewire::actingAs($user1)
        ->test(GlobalSearch::class)
        ->set('query', 'secret')
        ->assertDontSee('Secret Notes');
});

it('redirects to journal with highlight query param when journal is clicked', function () {
    $user = User::factory()->create();

    $journal = Journal::create([
        'user_id' => $user->id,
        'title' => 'My Great Day',
        'content' => 'Feeling super calm and peaceful today.',
        'mood' => 'calm',
        'summary' => 'Peaceful day.',
        'advice' => 'Enjoy the calmness.',
        'journal_date' => today(),
    ]);

    Livewire::actingAs($user)
        ->test(GlobalSearch::class)
        ->call('selectJournal', $journal->id)
        ->assertRedirect(route('journal', ['highlight' => $journal->id]));
});

it('filters journals on the journal page using search parameter', function () {
    $user = User::factory()->create();

    Journal::create([
        'user_id' => $user->id,
        'title' => 'Walking in Nature',
        'content' => 'Took a walk in the forest park.',
        'mood' => 'calm',
        'summary' => 'Nature walk.',
        'advice' => 'Stay connected to nature.',
        'journal_date' => today(),
    ]);

    Journal::create([
        'user_id' => $user->id,
        'title' => 'Gym Workout',
        'content' => 'Hit personal record on bench press.',
        'mood' => 'energetic',
        'summary' => 'Gym session.',
        'advice' => 'Rest well.',
        'journal_date' => today(),
    ]);

    Livewire::actingAs($user)
        ->test(JournalPage::class)
        ->set('search', 'forest')
        ->assertSee('Walking in Nature')
        ->assertDontSee('Gym Workout')
        ->call('clearSearch')
        ->assertSee('Walking in Nature')
        ->assertSee('Gym Workout');
});
