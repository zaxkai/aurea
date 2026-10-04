<?php

use App\Livewire\AiAurea;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('saves a user message and the assistant reply to the chat session', function () {
    $user = User::factory()->create();
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'I am here with you.']]]]],
        ]),
    ]);

    Livewire::actingAs($user)
        ->test(AiAurea::class)
        ->set('message', 'I feel stressed')
        ->call('sendMessage')
        ->assertSet('message', '')
        ->assertSee('I am here with you.')
        ->assertSee('AI_AUREA.png');

    $session = $user->chatSessions()->sole();
    $messages = $session->messages()->orderBy('id')->get();

    expect($messages->pluck('role')->all())->toBe(['user', 'assistant'])
        ->and($messages[0]->content)->toBe('I feel stressed')
        ->and($messages[1]->content)->toBe('I am here with you.')
        ->and($user->aiUsageLogs()->count())->toBe(1);
});

it('instructs Gemini to reply in the language of the latest chat message', function () {
    $user = User::factory()->create();
    $requestData = null;
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => function (Request $request) use (&$requestData) {
            $requestData = $request->data();

            return Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'I am here with you.']]]]],
            ]);
        },
    ]);

    app(ChatbotService::class)->chat($user, 'school because my teacher send a lot home work', [
        ['role' => 'user', 'content' => 'Aku sedang merasa sedih.'],
    ]);

    expect($requestData['system_instruction']['parts'][0]['text'])
        ->toContain('REQUIRED RESPONSE LANGUAGE: English')
        ->toContain('Write your entire response only in English')
        ->toContain('Ignore the language of this system prompt, earlier messages')
        ->and($requestData['contents'][1]['parts'][0]['text'])
        ->toBe('school because my teacher send a lot home work');
});

it('keeps Indonesian replies when the latest chat message is Indonesian', function () {
    $user = User::factory()->create();
    $requestData = null;
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => function (Request $request) use (&$requestData) {
            $requestData = $request->data();

            return Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Aku mendengarkan.']]]]],
            ]);
        },
    ]);

    app(ChatbotService::class)->chat($user, 'Aku lagi merasa capek banget', [
        ['role' => 'user', 'content' => 'I had a long day at school.'],
    ]);

    expect($requestData['system_instruction']['parts'][0]['text'])
        ->toContain('REQUIRED RESPONSE LANGUAGE: Indonesian')
        ->toContain('Write your entire response only in Indonesian')
        ->and($requestData['contents'][1]['parts'][0]['text'])
        ->toBe('Aku lagi merasa capek banget');
});

it('automatically processes a quick note forwarded from the dashboard', function () {
    $user = User::factory()->create();
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'I am sorry it was difficult. What felt hardest?']]]]],
        ]),
    ]);
    $this->withSession(['ai-aurea-prompt' => 'I had a difficult day.']);

    Livewire::actingAs($user)
        ->test(AiAurea::class)
        ->assertSet('message', 'I had a difficult day.')
        ->assertSet('shouldSendPrefilledMessage', true)
        ->call('sendPrefilledMessage')
        ->assertSet('message', '')
        ->assertSet('shouldSendPrefilledMessage', false)
        ->assertSee('I am sorry it was difficult. What felt hardest?');

    expect($user->chatSessions()->sole()->messages()->orderBy('id')->pluck('role')->all())
        ->toBe(['user', 'assistant']);

    expect($user->aiUsageLogs()->count())->toBe(1);
});

it('shows only the authenticated users own chat sessions in history', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $user->chatSessions()->create(['title' => 'My private conversation']);
    $otherUser->chatSessions()->create(['title' => 'Another users private conversation']);

    Livewire::actingAs($user)
        ->test(AiAurea::class)
        ->call('toggleHistory')
        ->assertSee('My private conversation')
        ->assertDontSee('Another users private conversation');
});

it('blocks free users at five prompts before sending another Gemini request', function () {
    $user = User::factory()->create();
    $usageDate = now('Asia/Jakarta')->toDateString();

    foreach (range(1, 5) as $promptNumber) {
        $user->aiUsageLogs()->create(['usage_date' => $usageDate]);
    }

    expect($user->aiUsageLogs()->whereDate('usage_date', $usageDate)->count())->toBe(5)
        ->and(app(ChatbotService::class)->remainingPrompts($user))->toBe(0);

    $requestCount = 0;
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => function (Request $request) use (&$requestCount) {
            $requestCount++;

            return Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Jawaban AI']]]]],
            ]);
        },
    ]);

    Livewire::actingAs($user)
        ->test(AiAurea::class)
        ->set('message', 'Pesan keenam')
        ->call('sendMessage')
        ->assertSee('Sisa 0 dari 5 chat hari ini')
        ->assertSee('Kuota chat hari ini sudah habis')
        ->assertSee('Buka Premium');

    expect($requestCount)->toBe(0);
    expect($user->chatSessions()->count())->toBe(0);
});

it('does not count a prompt when Gemini cannot return a successful reply', function () {
    $user = User::factory()->create();

    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([], 503),
    ]);

    Livewire::actingAs($user)
        ->test(AiAurea::class)
        ->set('message', 'Aku sedang merasa berat')
        ->call('sendMessage')
        ->assertSee('Coba chat lagi sebentar lagi ya.');

    expect($user->aiUsageLogs()->count())->toBe(0);
});

it('resets the free prompt count at midnight in Jakarta', function () {
    $user = User::factory()->create();

    $this->travelTo(new DateTimeImmutable('2026-10-01 16:59:59 UTC'));
    $user->aiUsageLogs()->create(['usage_date' => '2026-10-01']);

    expect(app(ChatbotService::class)->remainingPrompts($user))->toBe(4);

    $this->travelTo(new DateTimeImmutable('2026-10-01 17:00:00 UTC'));

    expect(app(ChatbotService::class)->remainingPrompts($user))->toBe(5);
});

it('allows premium users unlimited prompts without showing a counter', function () {
    $user = User::factory()->create();
    $user->activatePremium();

    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Aku mendengarkan.']]]]],
        ]),
    ]);

    $chat = Livewire::actingAs($user)->test(AiAurea::class);

    foreach (range(1, 6) as $promptNumber) {
        $chat->set('message', "Pesan {$promptNumber}")
            ->call('sendMessage');
    }

    $chat->assertSee('Premium')
        ->assertDontSee('Sisa 5 dari 5 chat hari ini');

    expect($user->chatSessions()->sole()->messages()->where('role', 'user')->count())->toBe(6)
        ->and($user->aiUsageLogs()->count())->toBe(0);
});
