<div class="mx-auto max-w-[1100px] space-y-5 pb-8" x-on:keydown.escape.window="$wire.closeModals()">
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold leading-tight text-navy">Journal</h1>
            <p class="mt-1 text-sm text-gray-500">Come sit down, and write your day with me!</p>
        </div>
        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <!-- Theme Customization Button -->
            <button type="button"
                    wire:click="openThemeModal"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-navy shadow-xs transition hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-aurea focus:ring-offset-2">
                <span class="text-sm leading-none">{{ $activeJournalTheme['icon'] ?? '🎨' }}</span>
                <span>Journal Theme</span>
                @if (auth()->user()->isPremium())
                    <span class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[9px] font-black text-amber-800 uppercase tracking-wide">PRO</span>
                @endif
            </button>

            <!-- Write Button -->
            <button type="button" wire:click="openWriteModal" aria-label="Write a journal entry" title="Write a journal entry" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-navy text-white transition hover:bg-navy/90 focus:outline-none focus:ring-2 focus:ring-aurea focus:ring-offset-2 shadow-xs">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
            </button>
        </div>
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
                    @php
                        $nextPoint = $chartPoints[$index + 1] ?? null;
                    @endphp
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

    <section aria-labelledby="journals-written-title" class="space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 id="journals-written-title" class="text-base font-semibold text-navy">Journals Written</h2>
                <p class="text-xs text-gray-400">Read and reflect on what you've created</p>
            </div>

            <!-- Inline Journal Search Input -->
            <div class="relative w-full sm:w-72">
                <svg class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text"
                       wire:model.live.debounce.250ms="search"
                       placeholder="Filter your notes..."
                       class="w-full bg-white border border-gray-100 rounded-xl py-2 pl-9 pr-8 text-xs text-navy placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-aurea/30 shadow-sm" />
                @if ($search !== '')
                    <button type="button" wire:click="clearSearch" aria-label="Clear journal search" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-navy">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                @endif
            </div>
        </div>

        @if ($search !== '')
            <div class="flex items-center justify-between rounded-xl bg-teal-50/70 border border-teal-100 px-3.5 py-2 text-xs text-teal-800">
                <span>Showing results for "<strong>{{ $search }}</strong>" ({{ $journals->count() }} found)</span>
                <button type="button" wire:click="clearSearch" class="font-semibold underline hover:text-navy">Show all</button>
            </div>
        @endif

        @if (session('journal-saved'))
            <p role="status" class="rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('journal-saved') }}</p>
        @endif

        @if (session('journal-theme-updated'))
            <p role="status" class="rounded-lg bg-teal-50 border border-teal-200 px-4 py-3 text-sm font-semibold text-teal-800 flex items-center gap-2">
                <span>✨</span>
                <span>{{ session('journal-theme-updated') }}</span>
            </p>
        @endif

        @forelse ($journals as $journal)
            @php
                $moodData = $moods[$journal->mood] ?? $moods['neutral'];
                $isHighlighted = ($highlightedJournalId === $journal->id);
                $isDefaultTheme = ($activeJournalThemeKey === 'default');
            @endphp
            @if ($isDefaultTheme)
                <article wire:key="journal-{{ $journal->id }}"
                         class="group flex min-h-[88px] items-center justify-between gap-4 rounded-2xl bg-white px-5 py-3.5 shadow-xs md:px-6 transition-all {{ $isHighlighted ? 'ring-2 ring-aurea shadow-md bg-cyan-50/30' : 'border border-gray-100/60 hover:shadow-sm hover:border-gray-200' }}">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-[11px] font-medium text-slate-400">{{ ($journal->journal_date ?? $journal->created_at)->format('F j, Y') }}</p>
                            @if ($isHighlighted)
                                <span class="rounded-full bg-aurea/30 text-navy px-2 py-0.5 text-[10px] font-bold">Selected from search</span>
                            @endif
                        </div>
                        <h3 class="mt-1 flex items-center gap-1.5 text-sm font-semibold text-navy">
                            <span class="h-3.5 w-1 shrink-0 rounded-full {{ $moodData['background'] }}" aria-hidden="true"></span>
                            <span class="truncate">{{ $journal->title }}</span>
                        </h3>
                        <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">“{{ \Illuminate\Support\Str::limit($journal->content, 180) }}”</p>
                    </div>
                    <img src="{{ asset('images/journal/'.$moodData['mascot']) }}" alt="{{ $moodData['label'] }} mood" class="h-16 w-16 shrink-0 object-contain transition-transform duration-200 group-hover:scale-105" loading="lazy">
                </article>
            @else
                <article wire:key="journal-{{ $journal->id }}"
                         class="group relative flex min-h-[96px] sm:min-h-[104px] items-center justify-between gap-4 rounded-2xl {{ $activeJournalTheme['card_class'] }} px-5 py-4 sm:px-6 sm:py-5 transition-all overflow-hidden {{ $isHighlighted ? 'ring-2 ring-aurea shadow-md scale-[1.01]' : '' }}">

                    {{-- Atmospheric FX Layer --}}
                    @if (!empty($activeJournalTheme['fx_type']))
                        <div class="journal-fx journal-fx--{{ $activeJournalTheme['fx_type'] }} pointer-events-none absolute inset-0 overflow-hidden z-0" aria-hidden="true">
                            @if ($activeJournalTheme['fx_type'] === 'sakura')
                                <span class="journal-petal"></span>
                                <span class="journal-petal"></span>
                                <span class="journal-petal"></span>
                                <span class="journal-petal"></span>
                                <span class="journal-petal"></span>
                                <span class="journal-petal"></span>
                                <span class="journal-petal"></span>
                                <span class="journal-petal"></span>
                            @elseif ($activeJournalTheme['fx_type'] === 'snow')
                                <span class="journal-snowflake"></span>
                                <span class="journal-snowflake"></span>
                                <span class="journal-snowflake"></span>
                                <span class="journal-snowflake"></span>
                                <span class="journal-snowflake"></span>
                                <span class="journal-snowflake"></span>
                                <span class="journal-snowflake"></span>
                                <span class="journal-snowflake"></span>
                            @elseif ($activeJournalTheme['fx_type'] === 'rain')
                                <span class="journal-raindrop"></span>
                                <span class="journal-raindrop"></span>
                                <span class="journal-raindrop"></span>
                                <span class="journal-raindrop"></span>
                                <span class="journal-raindrop"></span>
                                <span class="journal-raindrop"></span>
                                <span class="journal-raindrop"></span>
                                <span class="journal-raindrop"></span>
                                <span class="journal-raindrop"></span>
                            @endif
                        </div>
                    @endif

                    {{-- Text Content Area (Clean, High Contrast, 100% Readable) --}}
                    <div class="min-w-0 flex-1 z-10">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-[11px] {{ $activeJournalTheme['date_class'] }} flex items-center gap-1.5">
                                <span>{{ ($journal->journal_date ?? $journal->created_at)->format('F j, Y') }}</span>
                                @if (!empty($activeJournalTheme['date_badge']))
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold shadow-2xs {{ $activeJournalTheme['date_badge_class'] ?? 'bg-white/80 text-slate-700' }}">{{ $activeJournalTheme['date_badge'] }}</span>
                                @endif
                            </p>
                            @if ($isHighlighted)
                                <span class="rounded-full bg-navy text-white px-2 py-0.5 text-[10px] font-bold shadow-2xs">Selected</span>
                            @endif
                        </div>
                        <h3 class="mt-1 flex items-center gap-2 text-sm sm:text-base {{ $activeJournalTheme['title_class'] }}">
                            @if (!empty($activeJournalTheme['bar_style']))
                                <span class="h-4 w-1.5 shrink-0 rounded-full shadow-2xs" style="{{ $activeJournalTheme['bar_style'] }}" aria-hidden="true"></span>
                            @elseif (!empty($activeJournalTheme['bar_class']))
                                <span class="h-4 w-1.5 shrink-0 rounded-full {{ $activeJournalTheme['bar_class'] }} shadow-2xs" aria-hidden="true"></span>
                            @else
                                <span class="h-4 w-1.5 shrink-0 rounded-full {{ $moodData['background'] }}" aria-hidden="true"></span>
                            @endif
                            <span class="truncate">{{ $journal->title }}</span>
                        </h3>
                        <p class="mt-1 line-clamp-2 text-xs {{ $activeJournalTheme['content_class'] }}">“{{ \Illuminate\Support\Str::limit($journal->content, 180) }}”</p>
                    </div>

                    {{-- Clean Themed Mascot Medallion --}}
                    <div class="relative shrink-0 z-10">
                        <div class="relative h-16 w-16 sm:h-20 sm:w-20 rounded-2xl overflow-hidden border-2 border-white/90 bg-white/80 backdrop-blur-xs shadow-xs p-1 flex items-center justify-center transition-transform duration-300 group-hover:scale-105">
                            @if (!empty($activeJournalTheme['mascot_badge']))
                                <img src="{{ asset($activeJournalTheme['mascot_badge']) }}" alt="{{ $activeJournalTheme['name'] }} mascot" class="w-full h-full object-cover rounded-xl" loading="lazy">
                            @else
                                <img src="{{ asset('images/journal/'.$moodData['mascot']) }}" alt="{{ $moodData['label'] }} mood" class="w-full h-full object-contain" loading="lazy">
                            @endif

                            {{-- Mood mini indicator --}}
                            <span class="absolute bottom-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-white/95 shadow-xs border border-slate-100" title="{{ $moodData['label'] }} mood">
                                <img src="{{ asset('images/journal/'.$moodData['mascot']) }}" alt="{{ $moodData['label'] }}" class="h-3.5 w-3.5 object-contain">
                            </span>
                        </div>
                    </div>
                </article>
            @endif
        @empty
            <div class="rounded-lg bg-white px-5 py-8 text-center shadow-sm">
                @if ($search !== '')
                    <p class="text-sm font-semibold text-navy">No journal entries found</p>
                    <p class="mt-1 text-xs text-slate-500">No notes matched your search query "{{ $search }}".</p>
                    <button type="button" wire:click="clearSearch" class="mt-3 inline-flex items-center rounded-xl bg-navy px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-navy/90">
                        View all journals
                    </button>
                @else
                    <p class="text-sm font-semibold text-navy">Your journal starts here</p>
                    <p class="mt-1 text-xs text-slate-500">Choose the plus button when you're ready to write.</p>
                @endif
            </div>
        @endforelse
    </section>

    @if ($showWriteModal || $showSummaryModal || $showThemeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Full-screen Backdrop -->
            <div class="fixed inset-0 bg-navy/40 backdrop-blur-sm transition-opacity" wire:click="closeModals" aria-hidden="true"></div>

            <div class="relative flex min-h-full items-center justify-center p-4">
                @if ($showWriteModal)
                    <section role="dialog" aria-modal="true" aria-labelledby="write-journal-title" class="relative w-full max-w-[430px] rounded-[20px] border border-slate-200 bg-[#f5f7fc] p-5 shadow-2xl sm:p-7">
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
                                    <button type="button" wire:click="$set('mood', '{{ $key }}')" aria-pressed="{{ $mood === $key ? 'true' : 'false' }}" class="inline-flex min-h-9 items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-navy transition hover:brightness-95 {{ $moodOption['background'] }} {{ $mood === $key ? 'ring-2 ring-navy ring-offset-2' : '' }}">
                                        <div class="w-4 h-4 rounded-full bg-white/90 p-0.5 flex items-center justify-center overflow-hidden shrink-0">
                                            <img src="{{ asset('images/journal/' . $moodOption['mascot']) }}" alt="" class="w-full h-full object-contain">
                                        </div>
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
                        <h2 id="journal-summary-title" class="max-w-[310px] text-2xl font-bold leading-tight text-navy">Here's what I understood from your journal <span class="inline-block h-5 w-5 align-middle"><img src="{{ asset('images/journal/maskot-journal2.png') }}" alt="" class="h-full w-full object-contain"></span></h2>
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

            @if ($showThemeModal)
                <section role="dialog" aria-modal="true" aria-labelledby="journal-theme-title" class="my-auto w-full max-w-[720px] rounded-[24px] border border-slate-200 bg-[#f8fafc] p-5 shadow-2xl sm:p-7">
                    <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-200/80 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-navy/5 text-base">🎨</span>
                                <h2 id="journal-theme-title" class="text-xl sm:text-2xl font-black text-navy">Choose Journal Theme</h2>
                            </div>
                            <p class="mt-1 text-xs sm:text-sm text-slate-500">Customize your daily journal cards with your favorite visual theme.</p>
                        </div>
                        <button type="button" wire:click="closeModals" aria-label="Close" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-slate-200 text-navy transition hover:bg-slate-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" stroke-width="2" /></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[65vh] overflow-y-auto pr-1">
                        @foreach ($availableJournalThemes as $themeKey => $theme)
                            @php
                                $isActive = ($activeJournalThemeKey === $themeKey);
                                $isLocked = ($theme['is_premium'] && !auth()->user()->isPremium());
                            @endphp
                            <div class="relative flex flex-col justify-between rounded-2xl border-2 transition-all p-3.5 bg-white shadow-xs {{ $isActive ? 'border-navy ring-2 ring-navy/20 bg-slate-50/50' : ($isLocked ? 'border-slate-200 opacity-95' : 'border-slate-200 hover:border-slate-300 hover:shadow-sm') }}">
                                <div>
                                    <!-- Live Mini Card Preview -->
                                    <div class="relative min-h-[82px] w-full rounded-xl overflow-hidden p-3 transition-all flex items-center justify-between border {{ $theme['card_class'] }} mb-3">
                                        @if (!empty($theme['fx_type']))
                                            <div class="journal-fx journal-fx--{{ $theme['fx_type'] }} pointer-events-none absolute inset-0 overflow-hidden z-0" aria-hidden="true">
                                                @if ($theme['fx_type'] === 'sakura')
                                                    <span class="journal-petal"></span>
                                                    <span class="journal-petal"></span>
                                                    <span class="journal-petal"></span>
                                                    <span class="journal-petal"></span>
                                                    <span class="journal-petal"></span>
                                                    <span class="journal-petal"></span>
                                                @elseif ($theme['fx_type'] === 'snow')
                                                    <span class="journal-snowflake"></span>
                                                    <span class="journal-snowflake"></span>
                                                    <span class="journal-snowflake"></span>
                                                    <span class="journal-snowflake"></span>
                                                    <span class="journal-snowflake"></span>
                                                    <span class="journal-snowflake"></span>
                                                @elseif ($theme['fx_type'] === 'rain')
                                                    <span class="journal-raindrop"></span>
                                                    <span class="journal-raindrop"></span>
                                                    <span class="journal-raindrop"></span>
                                                    <span class="journal-raindrop"></span>
                                                    <span class="journal-raindrop"></span>
                                                    <span class="journal-raindrop"></span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="min-w-0 flex-1 z-10 pr-2">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] font-semibold {{ $theme['date_class'] }}">October 14, 2026</span>
                                                @if (!empty($theme['date_badge']))
                                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $theme['date_badge_class'] ?? 'bg-slate-100 text-slate-700' }}">{{ $theme['date_badge'] }}</span>
                                                @endif
                                            </div>
                                            <div class="mt-1 flex items-center gap-1.5">
                                                @if (!empty($theme['bar_style']))
                                                    <span class="h-3.5 w-1 shrink-0 rounded-full" style="{{ $theme['bar_style'] }}"></span>
                                                @elseif (!empty($theme['bar_class']))
                                                    <span class="h-3.5 w-1 shrink-0 rounded-full {{ $theme['bar_class'] }}"></span>
                                                @else
                                                    <span class="h-3.5 w-1 shrink-0 rounded-full bg-aurea"></span>
                                                @endif
                                                <span class="text-xs font-bold truncate {{ $theme['title_class'] }}">A quiet reflection</span>
                                            </div>
                                            <div class="mt-1 space-y-1">
                                                <div class="h-1.5 w-4/5 rounded-full bg-slate-200/70"></div>
                                            </div>
                                        </div>

                                        <!-- Right Mascot Badge -->
                                        <div class="relative shrink-0 z-10">
                                            @if (!empty($theme['mascot_badge']))
                                                <div class="h-12 w-12 rounded-xl overflow-hidden border border-white/90 shadow-2xs bg-white/80 p-0.5 flex items-center justify-center">
                                                    <img src="{{ asset($theme['mascot_badge']) }}" alt="{{ $theme['name'] }} mascot" class="w-full h-full object-cover rounded-lg" loading="lazy">
                                                </div>
                                            @else
                                                <img src="{{ asset('images/journal/maskot-journal1.png') }}" alt="Classic mascot" class="h-11 w-11 object-contain" loading="lazy">
                                            @endif
                                        </div>

                                        <!-- Top-Right Status Badge (Active / PRO / Free) -->
                                        <div class="absolute top-1.5 right-1.5 z-20">
                                            @if ($isActive)
                                                <span class="rounded-full bg-navy text-white text-[10px] font-extrabold px-2 py-0.5 shadow-sm flex items-center gap-1">
                                                    <span>✓</span>
                                                    <span>Active</span>
                                                </span>
                                            @elseif ($isLocked)
                                                <span class="rounded-full bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 shadow-sm flex items-center gap-1">
                                                    <span>🔒</span>
                                                    <span>PRO</span>
                                                </span>
                                            @else
                                                <span class="rounded-full bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 shadow-sm">
                                                    Free
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Theme Name & Description -->
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="text-base">{{ $theme['icon'] }}</span>
                                        <h3 class="text-sm font-bold text-navy">{{ $theme['name'] }}</h3>
                                    </div>
                                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ $theme['description'] }}</p>
                                </div>

                                <!-- Action Button -->
                                <div class="mt-4 pt-3 border-t border-slate-100">
                                    @if ($isActive)
                                        <button type="button" disabled class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 py-2 text-xs font-bold text-slate-500 cursor-default">
                                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>✓ In Use</span>
                                        </button>
                                    @elseif ($isLocked)
                                        <a href="{{ route('premium') }}" class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 py-2 text-xs font-bold text-white shadow-xs transition">
                                            <span>🔒</span>
                                            <span>Unlock with Premium</span>
                                        </a>
                                    @else
                                        <button type="button" wire:click="selectTheme('{{ $themeKey }}')" wire:loading.attr="disabled" class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-navy hover:bg-navy/90 py-2 text-xs font-bold text-white shadow-xs transition active:scale-[0.99] cursor-pointer">
                                            <span>Use This Theme</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 flex items-center justify-between text-xs text-slate-400 border-t border-slate-200/80 pt-3">
                        <span>Your selected theme will be applied to all your journal cards.</span>
                        <button type="button" wire:click="closeModals" class="font-bold text-navy hover:underline">Close</button>
                    </div>
                </section>
            @endif
            </div>
        </div>
    @endif
</div>