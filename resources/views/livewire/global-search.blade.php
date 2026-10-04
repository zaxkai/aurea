<div class="relative w-full" x-data="{ focused: false }" x-on:click.outside="$wire.set('isOpen', false); focused = false" x-on:keydown.escape.window="$wire.set('isOpen', false); focused = false">
    <!-- Search Bar Input Container -->
    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-[18px] flex items-center text-slate-400">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <input type="text"
               wire:model.live.debounce.250ms="query"
               wire:keydown.enter="submitSearch"
               x-on:focus="focused = true; if ($wire.query.trim().length > 0) $wire.set('isOpen', true)"
               placeholder="Search pages or journals..."
               autocomplete="off"
               class="w-full rounded-full border border-gray-200/90 bg-gray-50/90 py-2.5 pl-11 pr-11 text-sm font-medium text-navy placeholder-slate-400 transition-all focus:border-cyan-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-cyan-100/60 shadow-sm" />

        @if ($query !== '')
            <button type="button"
                    wire:click="resetSearch"
                    aria-label="Clear search"
                    class="absolute inset-y-0 right-[16px] flex items-center text-slate-400 hover:text-navy transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>

    <!-- Dropdown Results (100% Solid Background, No bleed through) -->
    @if ($isOpen && $query !== '')
        <div class="absolute left-0 right-0 z-50 mt-2 max-h-[460px] w-full min-w-[320px] sm:min-w-[420px] overflow-y-auto rounded-2xl border border-gray-200/90 bg-white p-2.5 shadow-2xl shadow-navy/20">
            
            @php
                $hasPages = $matchedPages->isNotEmpty();
                $hasJournals = $matchedJournals->isNotEmpty();
            @endphp

            @if (! $hasPages && ! $hasJournals)
                <div class="px-4 py-8 text-center bg-white">
                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-navy">No results found</p>
                    <p class="mt-1 text-xs text-gray-500">Could not find any pages or journals for "<span class="font-semibold text-navy">{{ $query }}</span>"</p>
                </div>
            @endif

            <!-- Pages Group -->
            @if ($hasPages)
                <div class="mb-3">
                    <div class="flex items-center justify-between px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            Pages & Features
                        </span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">{{ $matchedPages->count() }}</span>
                    </div>

                    <div class="mt-1 space-y-1">
                        @foreach ($matchedPages as $page)
                            <a href="{{ route($page['route']) }}"
                               wire:click.prevent="selectPage('{{ $page['route'] }}')"
                               class="group flex items-center justify-between gap-3 rounded-xl p-2.5 text-left transition hover:bg-gray-50 active:bg-gray-100 border border-transparent hover:border-gray-100">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl shadow-xs transition
                                        {{ $page['icon'] === 'home' ? 'bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white' : '' }}
                                        {{ $page['icon'] === 'journal' ? 'bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white' : '' }}
                                        {{ $page['icon'] === 'tree' ? 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white' : '' }}
                                        {{ $page['icon'] === 'ai' ? 'bg-cyan-50 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white' : '' }}
                                        {{ $page['icon'] === 'star' ? 'bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white' : '' }}
                                        {{ $page['icon'] === 'settings' ? 'bg-slate-100 text-slate-600 group-hover:bg-slate-700 group-hover:text-white' : '' }}
                                    ">
                                        @if ($page['icon'] === 'home')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <rect x="4" y="4" width="6" height="6" rx="1.5" />
                                                <rect x="14" y="4" width="6" height="6" rx="1.5" />
                                                <rect x="4" y="14" width="6" height="6" rx="1.5" />
                                                <rect x="14" y="14" width="6" height="6" rx="1.5" />
                                            </svg>
                                        @elseif ($page['icon'] === 'journal')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        @elseif ($page['icon'] === 'tree')
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2.8 6.7 9.1h2.8L5.2 14.7h4.5v3.1H8.1v2h7.8v-2h-1.6v-3.1h4.5l-4.3-5.6h2.8L12 2.8Z" />
                                            </svg>
                                        @elseif ($page['icon'] === 'ai')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        @elseif ($page['icon'] === 'star')
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-bold text-navy group-hover:text-cyan-700 transition">{{ $page['title'] }}</p>
                                        <p class="truncate text-[11px] text-gray-500">{{ $page['description'] }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-lg bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500 group-hover:bg-navy/10 group-hover:text-navy transition">
                                    {{ $page['badge'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Journal Entries Group -->
            @if ($hasJournals)
                <div class="border-t border-gray-100 pt-2.5">
                    <div class="flex items-center justify-between px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Journal Entries
                        </span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">{{ $matchedJournals->count() }}</span>
                    </div>

                    <div class="mt-1 space-y-1">
                        @foreach ($matchedJournals as $journal)
                            <a href="{{ route('journal', ['highlight' => $journal->id]) }}"
                               wire:click.prevent="selectJournal({{ $journal->id }})"
                               class="group flex flex-col gap-1 rounded-xl p-3 text-left transition hover:bg-cyan-50/40 active:bg-cyan-50/70 border border-transparent hover:border-cyan-100/60">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $moodColors[$journal->mood] ?? 'bg-gray-300' }}"></span>
                                        <h4 class="truncate text-xs font-bold text-navy group-hover:text-cyan-700 transition">
                                            {{ $journal->title }}
                                        </h4>
                                    </div>
                                    <span class="shrink-0 rounded-md bg-gray-50 px-1.5 py-0.5 text-[10px] font-medium text-gray-500">
                                        {{ ($journal->journal_date ?? $journal->created_at)->format('M j, Y') }}
                                    </span>
                                </div>

                                <p class="text-[11px] leading-relaxed text-gray-600 line-clamp-2">
                                    “{{ $this->getSnippet($journal->content ?? '', $query) }}”
                                </p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Bottom Helper Footer -->
            <div class="mt-2.5 border-t border-gray-100 px-3 pt-2 pb-0.5 flex items-center justify-between text-[10px] font-medium text-gray-400">
                <span class="flex items-center gap-1">
                    <kbd class="rounded border border-gray-200 bg-gray-50 px-1 py-0.5 text-[9px] text-gray-500">Enter</kbd> to select
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="rounded border border-gray-200 bg-gray-50 px-1 py-0.5 text-[9px] text-gray-500">Esc</kbd> to close
                </span>
            </div>

        </div>
    @endif
</div>
