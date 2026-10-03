<?php

namespace App\Livewire;

use App\Exceptions\AiPromptLimitReachedException;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\ChatbotService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class AiAurea extends Component
{
    public string $message = '';

    public ?int $activeSessionId = null;

    public array $messages = [];

    public array $sessions = [];

    public bool $showHistory = false;

    public bool $shouldSendPrefilledMessage = false;

    protected ChatbotService $chatbotService;

    public function boot(ChatbotService $chatbotService): void
    {
        $this->chatbotService = $chatbotService;
    }

    public function mount(): void
    {
        $this->refreshSessions();

        $latestSession = auth()->user()->chatSessions()->latest('updated_at')->first();

        if ($latestSession) {
            $this->loadSession($latestSession->id);
        }

        $incomingPrompt = session()->pull('ai-aurea-prompt');

        if (is_string($incomingPrompt) && trim($incomingPrompt) !== '') {
            $this->message = trim($incomingPrompt);
            $this->shouldSendPrefilledMessage = true;
        }
    }

    public function sendMessage(ChatbotService $chatbot): void
    {
        $validated = $this->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $content = trim($validated['message']);

        if ($content === '') {
            $this->addError('message', 'Tulis pesan terlebih dahulu.');

            return;
        }

        $user = auth()->user();
        $session = $this->activeSessionId
            ? $user->chatSessions()->findOrFail($this->activeSessionId)
            : null;

        $history = $session
            ? $session->messages()
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->reverse()
                ->map(fn (ChatMessage $chatMessage): array => [
                    'role' => $chatMessage->role,
                    'content' => $chatMessage->content,
                ])
                ->all()
            : [];

        try {
            $reply = $chatbot->chat($user, $content, $history);
        } catch (AiPromptLimitReachedException) {
            $this->message = $content;

            return;
        }

        $session ??= $user->chatSessions()->create(['title' => Str::limit($content, 54)]);

        $session->messages()->create([
            'role' => 'user',
            'content' => $content,
        ]);

        $session->messages()->create([
            'role' => 'assistant',
            'content' => $reply,
        ]);

        $this->message = '';
        $this->showHistory = false;
        $this->loadSession($session->id);
        $this->refreshSessions();
    }

    public function sendPrefilledMessage(ChatbotService $chatbot): void
    {
        if (! $this->shouldSendPrefilledMessage) {
            return;
        }

        $this->shouldSendPrefilledMessage = false;
        $this->sendMessage($chatbot);
    }

    public function newChat(): void
    {
        $this->activeSessionId = null;
        $this->messages = [];
        $this->message = '';
        $this->showHistory = false;
        $this->resetErrorBag();
    }

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;

        if ($this->showHistory) {
            $this->refreshSessions();
        }
    }

    public function loadSession(int $sessionId): void
    {
        $session = auth()->user()->chatSessions()
            ->with('messages')
            ->findOrFail($sessionId);

        $this->activeSessionId = $session->id;
        $this->messages = $session->messages
            ->map(fn (ChatMessage $chatMessage): array => [
                'id' => $chatMessage->id,
                'role' => $chatMessage->role,
                'content' => $chatMessage->content,
            ])
            ->all();
        $this->showHistory = false;
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.ai-aurea', [
            'firstName' => explode(' ', $user->name)[0],
            'isPremium' => $user->isPremium(),
            'dailyPromptLimit' => $this->chatbotService->dailyPromptLimit(),
            'remainingPrompts' => $this->chatbotService->remainingPrompts($user),
        ]);
    }

    private function refreshSessions(): void
    {
        $this->sessions = auth()->user()->chatSessions()
            ->latest('updated_at')
            ->get()
            ->map(fn (ChatSession $session): array => [
                'id' => $session->id,
                'title' => $session->title ?: 'Percakapan baru',
                'updated_at' => $session->updated_at->diffForHumans(),
            ])
            ->all();
    }
}
