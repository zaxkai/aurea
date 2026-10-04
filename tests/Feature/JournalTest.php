<?php

use App\Livewire\JournalPage;
use App\Models\Journal;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('instructs Gemini to summarize a journal in the language of its content', function () {
    $requestData = null;
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => function (Request $request) use (&$requestData) {
            $requestData = $request->data();

            return Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{"summary":"A difficult day.","advice":"Take a short break."}']]]]],
            ]);
        },
    ]);

    app(ChatbotService::class)->summarizeJournal('I had a difficult day.', 'stressed');

    expect($requestData['system_instruction']['parts'][0]['text'])
        ->toContain('bahasa utama yang sama dengan isi catatan jurnal')
        ->and($requestData['contents'][0]['parts'][0]['text'])
        ->toContain('I had a difficult day.');
});

it('shows only the authenticated users journal entries', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->journals()->create([
        'title' => 'My private entry',
        'content' => 'A note from my day.',
        'mood' => 'calm',
        'summary' => 'A quiet day.',
        'advice' => 'Take a gentle pause.',
        'journal_date' => today(),
    ]);
    $otherUser->journals()->create([
        'title' => 'Another private entry',
        'content' => 'A different note.',
        'mood' => 'stressed',
        'summary' => 'A difficult day.',
        'advice' => 'Ask for support.',
        'journal_date' => today(),
    ]);

    Livewire::actingAs($user)
        ->test(JournalPage::class)
        ->assertSee('My private entry')
        ->assertDontSee('Another private entry');
});

it('generates a summary and saves the journal only after confirmation', function () {
    $user = User::factory()->create();
    $chatbot = Mockery::mock(ChatbotService::class);
    $chatbot->shouldReceive('summarizeJournal')
        ->once()
        ->with('My project did not go as planned.', 'stressed')
        ->andReturn([
            'summary' => 'You felt stressed about your project.',
            'advice' => 'Take a short break before trying again.',
        ]);

    app()->instance(ChatbotService::class, $chatbot);

    $component = Livewire::actingAs($user)
        ->test(JournalPage::class)
        ->call('openWriteModal')
        ->set('mood', 'stressed')
        ->set('content', 'My project did not go as planned.')
        ->call('generateSummary')
        ->assertSet('showSummaryModal', true)
        ->assertSee('You felt stressed about your project.')
        ->assertSee('Take a short break before trying again.');

    expect($user->journals()->count())->toBe(0);

    $component->call('saveJournal')
        ->assertSet('showSummaryModal', false)
        ->assertSet('content', '');

    $journal = $user->journals()->sole();

    expect($journal->title)->toBe('Stressed mind')
        ->and($journal->content)->toBe('My project did not go as planned.')
        ->and($journal->summary)->toBe('You felt stressed about your project.')
        ->and($journal->advice)->toBe('Take a short break before trying again.')
        ->and($journal->journal_date->toDateString())->toBe(today()->toDateString());
});

it('requires a valid mood and journal entry before requesting a summary', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(JournalPage::class)
        ->set('mood', 'unknown')
        ->set('content', '')
        ->call('generateSummary')
        ->assertHasErrors(['mood', 'content'])
        ->assertSet('showSummaryModal', false);

    expect(Journal::query()->count())->toBe(0);
});
