<div class="mx-auto max-w-[1100px] space-y-5 pb-8" x-on:keydown.escape.window="$wire.closeModals()">
    <header class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold leading-tight text-navy">Journal</h1>
            <p class="mt-1 text-sm text-gray-500">Come sit down, and write your day with me!</p>
        </div>
        <button type="button" wire:click="openWriteModal" aria-label="Write a journal entry" title="Write a journal entry" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-navy text-white transition hover:bg-navy/90 focus:outline-none focus:ring-2 focus:ring-aurea focus:ring-offset-2">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
            </svg>
        </button>
    </header>

    <section class="rounded-lg bg-white px-5 py-4 shadow-sm md:px-7" aria-labelledby="mood-statistics-title">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="mood-statistics-title" class="text-base font-semibold text-navy">Mood Statistics</h2>
                <p class="mt-0.5 text-xs text-gray-400">See how your mood goes along the days!</p>
            </div>
            <span class="pt-1 text-xs text-gray-400">Last 10 days</span>
        </div>

        <div class="mt-4 flex min-w-0 items-start gap-2">
            <img src="{{ asset('images/journal/emoji-statistik.png') }}" alt="Mood levels from happy to sad" class="mt-1 h-28 w-5 shrink-0 object-contain">
            <div class="min-w-0 flex-1 overflow-x-auto">
                <svg viewBox="0 0 790 190" class="h-40 min-w-[620px] w-full" role="img" aria-label="Mood trend for the last ten days">
                <line x1="48" y1="138" x2="741" y2="138" stroke="#eef1f7" stroke-width="1" />
                <line x1="48" y1="90" x2="741" y2="90" stroke="#f2f4f8" stroke-width="1" />
                <line x1="48" y1="42" x2="741" y2="42" stroke="#eef1f7" stroke-width="1" />

                @foreach ($chartPoints as $index => $point)
                    @php($nextPoint = $chartPoints[$index + 1] ?? null)
                    @if ($point['mood'] && $nextPoint && $nextPoint['mood'])
                        <line x1="{{ $point['x'] }}" y1="{{ $point['y'] }}" x2="{{ $nextPoint['x'] }}" y2="{{ $nextPoint['y'] }}" stroke="{{ $nextPoint['color'] }}" stroke-width="3" stroke-linecap="round" />
                    @endif
                    @if ($point['mood'])
                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="{{ $point['color'] }}" stroke="white" stroke-width="2" />
                    @endif
                    <text x="{{ $point['x'] }}" y="177" text-anchor="middle" fill="#8490a7" font-size="10">{{ $point['label'] }}</text>
                @endforeach

                @if ($journals->isEmpty())
                    <text x="395" y="94" text-anchor="middle" fill="#a2adbf" font-size="12">Your mood history will appear here</text>
                @endif
                </svg>
            </div>
        </div>
    </section>

    <section aria-labelledby="journals-written-title" class="space-y-2.5">
        <h2 id="journals-written-title" class="text-base font-semibold text-navy">Journals Written</h2>

        @if (session('journal-saved'))
            <p role="status" class="rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('journal-saved') }}</p>
        @endif

        @forelse ($journals as $journal)
            @php($moodData = $moods[$journal->mood] ?? $moods['neutral'])
            <article wire:key="journal-{{ $journal->id }}" class="flex min-h-[88px] items-center justify-between gap-4 rounded-lg bg-white px-5 py-3.5 shadow-sm md:px-6">
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-400">{{ ($journal->journal_date ?? $journal->created_at)->format('F j') }}</p>
                    <h3 class="mt-1 flex items-center gap-1.5 text-sm font-semibold text-navy">
                        <span class="h-3.5 w-1 shrink-0 rounded-full {{ $moodData['background'] }}" aria-hidden="true"></span>
                        <span class="truncate">{{ $journal->title }}</span>
                    </h3>
                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">“{{ \Illuminate\Support\Str::limit($journal->content, 150) }}”</p>
                </div>
                <img src="{{ asset('images/journal/'.$moodData['mascot']) }}" alt="{{ $moodData['label'] }} mood" class="h-16 w-16 shrink-0 object-contain" loading="lazy">
            </article>
        @empty
            <div class="rounded-lg bg-white px-5 py-8 text-center shadow-sm">
                <p class="text-sm font-semibold text-navy">Your journal starts here</p>
                <p class="mt-1 text-xs text-slate-500">Choose the plus button when you're ready to write.</p>
            </div>
        @endforelse
    </section>

    @if ($showWriteModal || $showSummaryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-navy/20 p-4 backdrop-blur-sm" wire:click.self="closeModals">
            @if ($showWriteModal)
                <section role="dialog" aria-modal="true" aria-labelledby="write-journal-title" class="my-auto w-full max-w-[430px] rounded-[20px] border border-slate-200 bg-[#f5f7fc] p-5 shadow-2xl sm:p-7">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h2 id="write-journal-title" class="text-2xl font-bold text-navy">Write Journal</h2>
                        <button type="button" wire:click="closeModals" aria-label="Close" class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-200 text-navy transition hover:bg-slate-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" stroke-width="2" /></svg>
                        </button>
                    </div>

                    <form wire:submit="generateSummary" class="space-y-5">
                        <fieldset>
                            <legend class="mb-2 text-base font-medium text-navy">I have been feeling...</legend>
                            <div class="flex flex-wrap justify-center gap-2">
                                @foreach ($moods as $key => $moodOption)
                                    <button type="button" wire:click="$set('mood', '{{ $key }}')" aria-pressed="{{ $mood === $key ? 'true' : 'false' }}" class="inline-flex min-h-9 items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold text-navy transition hover:brightness-95 {{ $moodOption['background'] }} {{ $mood === $key ? 'ring-2 ring-navy ring-offset-2' : '' }}">
                                        <span aria-hidden="true">{{ $moodOption['emoji'] }}</span>
                                        {{ $moodOption['label'] }}
                                    </button>
                                @endforeach
                            </div>
                            @error('mood') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </fieldset>

                        <div>
                            <label for="journal-content" class="mb-2 block text-base font-medium text-navy">What made you feel this way?</label>
                            <textarea id="journal-content" wire:model="content" maxlength="5000" rows="7" placeholder="Write anything that's on your mind. You can be as detailed as you'd like..." class="w-full resize-y rounded-2xl border-2 border-white bg-transparent px-4 py-3 text-sm leading-5 text-navy placeholder:text-slate-400 focus:border-aurea focus:outline-none focus:ring-0"></textarea>
                            @error('content') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="generateSummary" class="flex min-h-11 w-full items-center justify-center rounded-xl bg-navy px-4 text-sm font-semibold text-white transition hover:bg-navy/90 disabled:cursor-wait disabled:opacity-70">
                            <span wire:loading.remove wire:target="generateSummary">Save Journal</span>
                            <span wire:loading wire:target="generateSummary">Preparing your summary...</span>
                        </button>
                    </form>
                </section>
            @endif

            @if ($showSummaryModal)
                <section role="dialog" aria-modal="true" aria-labelledby="journal-summary-title" class="my-auto w-full max-w-[430px] rounded-[20px] border border-slate-200 bg-[#f5f7fc] p-5 shadow-2xl sm:p-7">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <h2 id="journal-summary-title" class="max-w-[310px] text-2xl font-bold leading-tight text-navy">Here's what I understood from your journal <span class="inline-block h-5 w-5 align-middle"><img src="{{ asset('images/journal/maskot-journal1.png') }}" alt="" class="h-full w-full object-contain"></span></h2>
                        <button type="button" wire:click="closeModals" aria-label="Close" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-slate-200 text-navy transition hover:bg-slate-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" stroke-width="2" /></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <section class="rounded-2xl border-2 border-white px-5 py-4">
                            <h3 class="font-bold text-navy">Summary</h3>
                            <p class="mt-1 whitespace-pre-line text-sm leading-5 text-navy">{{ $summary }}</p>
                        </section>
                        <section class="rounded-2xl border-2 border-white bg-[#e2eaff] px-5 py-4">
                            <h3 class="font-bold text-navy">Advice ✦</h3>
                            <p class="mt-1 whitespace-pre-line text-sm leading-5 text-navy">{{ $advice }}</p>
                        </section>
                    </div>

                    <div class="mt-5 flex gap-3">
                        <button type="button" wire:click="returnToWriting" class="min-h-11 flex-1 rounded-xl border border-slate-300 px-3 text-sm font-semibold text-navy transition hover:bg-white">Edit</button>
                        <button type="button" wire:click="saveJournal" wire:loading.attr="disabled" wire:target="saveJournal" class="min-h-11 flex-[2] rounded-xl bg-navy px-4 text-sm font-semibold text-white transition hover:bg-navy/90 disabled:cursor-wait disabled:opacity-70">
                            <span wire:loading.remove wire:target="saveJournal">Save Journal</span>
                            <span wire:loading wire:target="saveJournal">Saving...</span>
                        </button>
                    </div>
                </section>
            @endif
        </div>
    @endif
</div>