<div @if ($shouldSendPrefilledMessage) wire:init="sendPrefilledMessage" @endif class="relative flex min-h-full flex-col overflow-hidden bg-gradient-to-b from-[#f2f4fa] via-[#f2f4fa] to-[#64ded9]">
    <div class="relative z-20 flex items-center justify-end gap-2">
        <button type="button" wire:click="newChat" aria-label="Percakapan baru" title="Percakapan baru" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-navy shadow-sm transition hover:bg-white">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
            </svg>
        </button>
        <button type="button" wire:click="toggleHistory" class="rounded-full bg-navy px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-navy/90">
            Riwayat Chat
        </button>

        @if ($showHistory)
            <div class="absolute right-0 top-12 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-4 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-bold text-navy">Riwayat Chat</h2>
                    <button type="button" wire:click="toggleHistory" aria-label="Tutup riwayat chat" class="flex h-8 w-8 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-navy">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="max-h-72 space-y-1 overflow-y-auto">
                    @forelse ($sessions as $session)
                        <button type="button" wire:key="chat-session-{{ $session['id'] }}" wire:click="loadSession({{ $session['id'] }})" class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-gray-50">
                            <span class="truncate text-sm font-medium text-navy">{{ $session['title'] }}</span>
                            <span class="shrink-0 text-[10px] text-gray-400">{{ $session['updated_at'] }}</span>
                        </button>
                    @empty
                        <p class="py-6 text-center text-sm text-gray-400">Belum ada percakapan.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col">
        <header class="flex shrink-0 flex-col items-center pt-3 text-center">
            <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-white p-3 shadow-sm">
                <img src="{{ asset('images/AI_AUREA.png') }}" alt="Aurea" class="h-full w-full object-contain">
            </div>
            <h1 class="mt-2 text-2xl font-bold text-navy">AI Aurea</h1>
            <div class="mt-3">
                @if ($isPremium)
                    <span class="inline-flex rounded-full bg-[#00bfc3] px-4 py-1.5 text-xs font-semibold text-white">Premium</span>
                @else
                    <span class="text-xs font-medium text-gray-600">Sisa {{ $remainingPrompts }} dari {{ $dailyPromptLimit }} chat hari ini</span>
                @endif
            </div>
        </header>

        <section class="flex flex-1 flex-col justify-center gap-6 py-8" aria-label="Percakapan dengan AI Aurea">
            @if ($shouldSendPrefilledMessage)
                <div role="status" class="rounded-2xl bg-white/80 px-5 py-4 text-center text-sm font-medium text-navy shadow-sm">
                    <span wire:loading.remove wire:target="sendPrefilledMessage">Mengirim ceritamu ke Aurea...</span>
                    <span wire:loading.flex wire:target="sendPrefilledMessage" class="items-center justify-center gap-2">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-aurea"></span>
                        Aurea sedang menyusun jawaban...
                    </span>
                </div>
            @elseif (count($messages) === 0)
                <p class="text-center text-2xl font-medium leading-snug text-navy md:text-3xl">Hai {{ $firstName }}, apa yang ingin kamu ceritakan hari ini?</p>
            @else
                <div class="max-h-[52dvh] space-y-5 overflow-y-auto px-1 py-2" aria-live="polite">
                    @foreach ($messages as $chatMessage)
                        <div wire:key="chat-message-{{ $chatMessage['id'] }}" class="flex {{ $chatMessage['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            @if ($chatMessage['role'] === 'assistant')
                                <div class="mr-3 mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white p-1.5">
                                    <img src="{{ asset('images/AI-logo_aurea.png') }}" alt="Aurea" class="h-full w-full object-contain">
                                </div>
                            @endif
                            <p class="max-w-[85%] whitespace-pre-line rounded-[24px] px-5 py-3 text-sm leading-relaxed {{ $chatMessage['role'] === 'user' ? 'bg-[#64ded9] text-navy' : 'bg-white/80 text-navy shadow-sm' }}">{{ $chatMessage['content'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        @if (! $isPremium && $remainingPrompts === 0)
            <div role="status" class="mx-auto mb-3 w-full rounded-2xl bg-white/85 px-5 py-4 text-center text-sm text-navy shadow-sm">
                <p>Kuota chat hari ini sudah habis. Kamu bisa lanjut besok, atau membuka Premium untuk chat tanpa batas.</p>
                <a href="{{ route('premium') }}" class="mt-2 inline-flex min-h-9 items-center justify-center rounded-full bg-navy px-4 text-xs font-semibold text-white transition hover:bg-navy/90">Buka Premium</a>
            </div>
        @endif

        <form wire:submit.prevent="sendMessage" class="mx-auto flex w-full items-center gap-2 rounded-full bg-white p-2 pl-6 shadow-[0_12px_35px_rgba(6,24,55,0.12)]">
            <label for="ai-message" class="sr-only">Pesan untuk Aurea</label>
            <input id="ai-message" type="text" wire:model="message" placeholder="Tulis pesanmu di sini..." maxlength="2000" @disabled(! $isPremium && $remainingPrompts === 0) class="min-w-0 flex-1 border-0 bg-transparent py-2 text-sm text-navy placeholder:text-gray-400 focus:ring-0 disabled:cursor-not-allowed disabled:opacity-50">
            <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage" @disabled(! $isPremium && $remainingPrompts === 0) aria-label="Kirim pesan" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-navy text-white transition hover:bg-navy/90 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 4 16 8-16 8 3-8-3-8zm3 8h13" />
                </svg>
            </button>
        </form>
        @error('message')
            <p class="mx-auto mt-2 w-full max-w-4xl px-5 text-xs font-medium text-rose-700">{{ $message }}</p>
        @enderror
    </div>
</div>