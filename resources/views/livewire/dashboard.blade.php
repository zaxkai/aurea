<div class="space-y-5 max-w-[1440px] mx-auto pt-2">
    <!-- Main Grid Layout: Left Content (2 Cols) & Right Calendar -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Left & Center Column (9 cols on lg) -->
        <div class="lg:col-span-9 space-y-6">

            <!-- Greeting Header -->
            <div class="pt-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl xl:text-[2.2rem] font-extrabold text-navy tracking-[-0.04em]">
                        Welcome, {{ explode(' ', auth()->user()->name)[0] }}
                    </h1>
                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-sm font-bold text-navy shadow-sm border border-gray-100">
                        <img src="{{ asset('images/logo-api.png') }}" alt="" class="h-4 w-4 object-contain">
                        {{ auth()->user()->current_streak ?? 30 }}
                    </span>
                </div>
                <p class="text-gray-500 text-sm mt-1.5">Have you check your condition today?</p>
            </div>

            <!-- Hero Check-In Card with Cute Mascot -->
            <div class="relative overflow-hidden rounded-[30px] bg-[#011b3d] p-6 md:p-7 text-white shadow-xl shadow-navy/5">
                <div class="absolute right-4 -bottom-5 md:right-7 md:-bottom-7 w-32 h-40 md:w-40 md:h-48 pointer-events-none select-none">
                    <img src="{{ asset('images/AI_aurea_dashbord.png') }}" alt="" class="h-full w-full object-contain drop-shadow-xl">
                </div>

                <div class="relative z-10 max-w-lg">
                    <h2 class="text-xl md:text-2xl font-bold mb-4">Tell me about your day!</h2>

                    <form wire:submit.prevent="submitQuickNote" class="flex items-center gap-2.5">
                        <div class="relative flex-1">
                            <input type="text"
                                   wire:model="note"
                                   placeholder="I feel..."
                                   class="w-full bg-[#1b2d52] rounded-full py-3 px-5 text-sm text-white placeholder-gray-400 border border-white/10 focus:outline-none focus:ring-2 focus:ring-aurea/60">
                        </div>
                        <button type="submit"
                                class="w-11 h-11 rounded-full bg-[#D6E64C] text-navy font-bold flex items-center justify-center hover:scale-105 active:scale-95 transition shadow-md shadow-lime-500/20"
                                title="Kirim catatan">
                            <svg class="w-5 h-5 -rotate-45 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="button"
                                wire:click="openDetailedModal"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-cyan-300 hover:text-white transition underline underline-offset-4">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            {{ __('Input Complete Check-in (Sleep, Activity, Screen Time)') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mood Selector Pills (Row of 5) -->
            <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
                @foreach ($moods as $key => $mood)
                    <button type="button"
                            wire:click="checkIn('{{ $key }}')"
                            class="min-w-[120px] px-5 py-2.5 rounded-full {{ $mood['bg'] }} {{ $mood['text'] }} font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-150 shrink-0 shadow-sm hover:opacity-95 hover:scale-[1.02] active:scale-95 {{ $todayMood === $key ? 'ring-4 ring-navy ring-offset-2 ring-offset-bg shadow-md' : 'opacity-90' }}">
                        <span class="text-base">{{ $mood['emoji'] }}</span>
                        <span>{{ $mood['label'] }}</span>
                    </button>
                @endforeach
            </div>

            <!-- AI Insight & Well-being Index Banner (When Check-in is Active) -->
            @if ($todayCheckIn && $todayCheckIn->wellbeing_index !== null)
                <div class="bg-gradient-to-r from-teal-500/10 via-cyan-500/10 to-indigo-500/10 border border-teal-200/60 rounded-3xl p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex flex-col items-center justify-center border border-teal-100">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">INDEX</span>
                            <span class="text-lg font-black text-navy">{{ $todayCheckIn->wellbeing_index }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-800">
                                    {{ $todayCheckIn->wellbeing_category }}
                                </span>
                                @if ($warningLevel !== 'none')
                                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $warningLevel === 'high' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">
                                        ⚠️ Early Warning: {{ ucfirst($warningLevel) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-navy font-medium mt-1 max-w-xl">
                                💡 <span class="font-bold">AI Insight:</span> {{ $todayCheckIn->ai_insight }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="openDetailedModal"
                            class="text-xs font-bold text-navy bg-white px-3.5 py-2 rounded-xl border border-gray-200 shadow-sm hover:bg-gray-50 transition shrink-0">
                        {{ __('Update Data') }}
                    </button>
                </div>
            @endif

            <!-- 2-by-2 Grid: Streak Card & Today's Checklist -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Streak Card (Navy) -->
                <div class="bg-navy rounded-3xl p-7 text-white text-center flex flex-col items-center justify-center shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center mb-3">
                        <span class="text-3xl">🔥</span>
                    </div>
                    <div class="text-5xl font-black tracking-tight mb-1 text-white">
                        {{ auth()->user()->current_streak ?? 30 }}
                    </div>
                    <p class="font-bold text-base text-white">Days of Streak</p>
                    <p class="text-xs text-gray-400 mt-1">You're doing great, stay on fire!</p>
                </div>

                <!-- Today's Checklist (White Card) -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100/60 flex flex-col justify-between">
                    <div>
                        <div class="flex items-baseline justify-between mb-4">
                            <h3 class="font-bold text-base text-navy">Today's Checklist</h3>
                            <span class="text-[11px] text-gray-400 font-medium">Check it if you had done it!</span>
                        </div>

                        <div class="space-y-3">
                            @forelse ($checklistHabits as $habit)
                                @php
                                    $log = $habit->logs->first();
                                    $isDone = ($log->value_logged ?? 0) >= $habit->target_value;
                                @endphp
                                <div class="flex items-center justify-between px-4 py-3 rounded-2xl bg-gray-50/80 border border-gray-100 hover:bg-gray-100/70 transition">
                                    <span class="text-xs font-semibold {{ $isDone ? 'line-through text-gray-400' : 'text-navy' }}">
                                        {{ $habit->name }}
                                    </span>
                                    <button type="button"
                                            wire:click="toggleHabit({{ $habit->id }})"
                                            class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all {{ $isDone ? 'bg-navy border-navy text-white shadow-sm' : 'border-gray-300 hover:border-navy' }}">
                                        @if ($isDone)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                    </button>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs text-gray-400">
                                    {{ __('No daily checklist yet.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <p class="mt-4 text-center text-[11px] font-medium text-gray-400">{{ __('Curated from your profile') }}</p>
                </div>

                <!-- Habit Tracker (White Card) -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100/60 flex flex-col justify-between">
                    <div>
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-base text-navy">{{ __('Habit Tracker') }}</h3>
                                <p class="mt-0.5 text-[11px] text-gray-400">
                                    @if ($isPremium)
                                        Unlimited <span class="sr-only">Tanpa batas</span>
                                    @else
                                        {{ $activeHabitCount }}/{{ $freeHabitLimit }} habits
                                    @endif
                                </p>
                            </div>
                            <button type="button" wire:click="openAddHabitModal" title="{{ __('Add habit') }}" aria-label="{{ __('Add habit') }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy text-white transition hover:bg-navy/90 focus:outline-none focus:ring-2 focus:ring-aurea focus:ring-offset-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-width="2" /></svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            @forelse ($progressHabits as $habit)
                                @php
                                    $log = $habit->logs->first();
                                    $currentVal = $log->value_logged ?? 0;
                                    $percent = min(100, (int) round(($currentVal / $habit->target_value) * 100));
                                    $isFull = $percent >= 100;
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <div>
                                            <p class="font-semibold text-navy">{{ $habit->name }}</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $currentVal }}/{{ $habit->target_value }} {{ $habit->unit }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                    wire:click="toggleHabit({{ $habit->id }})"
                                                    class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all {{ $isFull ? 'bg-navy border-navy text-white' : 'border-gray-300 hover:border-navy' }}"
                                                    title="{{ __('Add progress') }}">
                                                @if ($isFull)
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @else
                                                    <span class="text-[10px] text-gray-400 font-bold">+</span>
                                                @endif
                                            </button>
                                            @if ($habit->source === 'user')
                                                <button type="button" wire:click="removeCustomHabit({{ $habit->id }})" title="{{ __('Disable habit') }}" aria-label="{{ __('Disable') }} {{ $habit->name }}" class="flex h-6 w-6 items-center justify-center rounded-full text-gray-400 transition hover:bg-rose-50 hover:text-rose-600">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" /></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-navy h-2 rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs text-gray-400">
                                    {{ __('No active habit progress yet.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if (session('habit-message'))
                        <p role="status" class="mt-4 text-center text-[11px] font-medium text-emerald-700">{{ session('habit-message') }}</p>
                    @endif
                </div>

                <!-- Growth Tree Preview (White Card) -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100/60 flex flex-col items-center justify-between text-center">
                    <h3 class="font-bold text-base text-navy mb-2">Growth Tree Preview</h3>

                    <x-growth-tree :stage="$treeStage" compact />
                    <p class="text-xs font-medium text-gray-500">{{ $completedGrowthItemsCount }} {{ __('targets completed') }}</p>

                    <a href="{{ route('habit-growth-tree') }}" class="mt-2 text-xs font-semibold text-gray-500 transition hover:text-navy">
                        See full
                    </a>
                </div>

            </div>
        </div>

        <!-- Right Column: Calendar & Premium -->
        <div class="flex flex-col gap-4 lg:col-span-3">
            <section class="rounded-3xl bg-white p-4 shadow-sm md:p-5" aria-label="Mood calendar">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <button type="button" wire:click="previousMonth" aria-label="Previous month" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-navy transition hover:bg-gray-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6" />
                        </svg>
                    </button>
                    <h3 class="text-center text-base font-bold text-navy md:text-lg">{{ $calendarTitle }}</h3>
                    <button type="button" wire:click="nextMonth" aria-label="Next month" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-navy transition hover:bg-gray-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>

                <div class="mb-1.5 grid grid-cols-7 text-center text-[10px] font-medium text-navy md:text-xs">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                </div>

                <div class="grid grid-cols-7 gap-px overflow-hidden rounded-xl border border-navy bg-navy">
                    @foreach ($calendarDays as $day)
                        <div class="relative flex aspect-square items-center justify-center text-[10px] font-medium {{ $day['color_class'] }} {{ $day['is_current_month'] ? 'text-navy' : 'text-gray-300' }} {{ $day['is_today'] ? 'z-10 ring-2 ring-inset ring-navy' : '' }}"
                             title="{{ $day['date']->format('d M Y') }} - {{ $day['mood'] ?? 'No log' }}">
                            {{ $day['date']->day }}
                        </div>
                    @endforeach
                </div>
            </section>

            @if (auth()->user()->isPremium())
                <div aria-label="Premium Aktif" class="flex aspect-[412/668] flex-col items-center justify-center gap-4 rounded-3xl bg-[#e0e8ff] px-4 text-center">
                    <img src="{{ asset('images/logo_aurea.png') }}" alt="AUREA" class="h-9 w-28 object-contain">
                    <span class="rounded-full bg-[#00bfc3] px-5 py-3 text-sm font-semibold text-white">Premium Aktif</span>
                    <p class="max-w-[190px] text-xs leading-snug text-navy">Semua ruang untuk berkembang kini terbuka untukmu.</p>
                </div>
            @else
                <div class="relative isolate block aspect-[412/668] overflow-hidden rounded-3xl bg-[#e0e8ff] text-center">
                    <div class="relative z-10 flex h-full flex-col items-center px-3 pt-5">
                        <img src="{{ asset('images/logo_aurea.png') }}" alt="AUREA" class="h-9 w-28 object-contain">
                        <h3 class="mt-1 text-3xl font-bold leading-tight text-navy">Premium</h3>
                        <p class="mt-3 max-w-[190px] text-xs leading-snug text-navy">
                            Buka lebih banyak fitur,<br>
                            capai tujuanmu dan<br>
                            jadi versi terbaik dirimu.
                        </p>
                        <form method="POST" action="{{ route('premium.activate') }}" class="mt-5">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 rounded-full bg-navy px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#183a69] focus:outline-none focus:ring-2 focus:ring-navy focus:ring-offset-2">
                                Buka Premium
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </form>
                    </div>
                    <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-[46%] overflow-hidden">
                        <img src="{{ asset('images/aurea-premium-dashbord_aurea.png') }}" alt="" class="absolute bottom-0 left-0 h-auto w-full max-w-none">
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($showAddHabitModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-navy/50 p-4 backdrop-blur-sm" wire:click.self="closeAddHabitModal">
            <section role="dialog" aria-modal="true" aria-labelledby="add-habit-title" class="my-auto w-full max-w-md rounded-2xl border border-gray-100 bg-white p-5 shadow-2xl sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="add-habit-title" class="text-lg font-bold text-navy">Tambah habit pilihan</h2>
                        <p class="mt-1 text-xs text-gray-500">Tambahkan kebiasaan sesuai kebutuhanmu.</p>
                    </div>
                    <button type="button" wire:click="closeAddHabitModal" aria-label="Tutup" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-navy">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" stroke-width="2" /></svg>
                    </button>
                </div>

                <form wire:submit="addCustomHabit" class="space-y-4">
                    <div>
                        <label for="custom-habit-name" class="mb-1.5 block text-xs font-semibold text-navy">Nama habit</label>
                        <input id="custom-habit-name" type="text" maxlength="80" wire:model="customHabitName" placeholder="Contoh: Membaca selama 15 menit" class="w-full rounded-xl border-gray-200 text-sm text-navy placeholder:text-gray-400 focus:border-aurea focus:ring-aurea">
                        @error('customHabitName') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="custom-habit-target" class="mb-1.5 block text-xs font-semibold text-navy">Target harian</label>
                            <input id="custom-habit-target" type="number" min="1" max="10000" wire:model="customHabitTarget" class="w-full rounded-xl border-gray-200 text-sm text-navy focus:border-aurea focus:ring-aurea">
                            @error('customHabitTarget') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="custom-habit-unit" class="mb-1.5 block text-xs font-semibold text-navy">Satuan</label>
                            <input id="custom-habit-unit" type="text" maxlength="24" wire:model="customHabitUnit" placeholder="menit" class="w-full rounded-xl border-gray-200 text-sm text-navy placeholder:text-gray-400 focus:border-aurea focus:ring-aurea">
                            @error('customHabitUnit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeAddHabitModal" class="min-h-10 rounded-xl px-4 text-sm font-semibold text-gray-500 transition hover:bg-gray-100">Batal</button>
                        <button type="submit" class="min-h-10 rounded-xl bg-navy px-5 text-sm font-semibold text-white transition hover:bg-navy/90">Tambah habit</button>
                    </div>
                </form>
            </section>
        </div>
    @endif

    @if ($showHabitUpgradeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-navy/50 p-4 backdrop-blur-sm" wire:click.self="closeHabitUpgradeModal">
            <section role="dialog" aria-modal="true" aria-labelledby="habit-upgrade-title" class="my-auto w-full max-w-md rounded-2xl border border-gray-100 bg-white p-5 shadow-2xl sm:p-6">
                <div class="flex flex-col items-center gap-4 text-center">
                    <div>
                        <h2 id="habit-upgrade-title" class="text-lg font-bold text-navy">Habit Free kamu sudah penuh</h2>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">Kamu sudah memakai {{ $freeHabitLimit }} habit. Buka Premium untuk habit tanpa batas.</p>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:flex-row sm:justify-center">
                        <a href="{{ route('premium') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-navy px-5 text-sm font-semibold text-white transition hover:bg-navy/90">Buka Premium</a>
                        <button type="button" wire:click="closeHabitUpgradeModal" class="min-h-10 rounded-xl px-5 text-sm font-semibold text-gray-500 transition hover:bg-gray-100">Nanti dulu</button>
                    </div>
                </div>
            </section>
        </div>
    @endif

    <!-- Modal Check-in Lengkap (Evidence-Based Scoring Inputs) -->
    @if ($showDetailedModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-navy/60 p-3 backdrop-blur-sm sm:items-center sm:p-4">
            <div class="relative my-auto max-h-[calc(100dvh-1.5rem)] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-100 bg-white p-5 shadow-2xl animate-in fade-in zoom-in duration-150 sm:max-h-[calc(100dvh-2rem)] sm:rounded-3xl sm:p-7">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-navy">Daily Evidence Check-In</h3>
                        <p class="text-xs text-gray-400">Masukkan indikator kesehatanmu untuk menghitung Well-being Index</p>
                    </div>
                    <button wire:click="closeDetailedModal" class="text-gray-400 hover:text-navy text-xl">
                        &times;
                    </button>
                </div>

                <div class="py-5 space-y-4">
                    <!-- Mood selection in modal -->
                    <div>
                        <label class="block text-xs font-bold text-navy mb-2">Pilih Mood Utama</label>
                        <div class="flex gap-2 flex-wrap">
                            @foreach ($moods as $key => $mood)
                                <button type="button"
                                        wire:click="$set('todayMood', '{{ $key }}')"
                                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold {{ $mood['bg'] }} {{ $mood['text'] }} {{ $todayMood === $key ? 'ring-2 ring-navy' : 'opacity-80' }}">
                                    {{ $mood['emoji'] }} {{ $mood['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Sleep Duration -->
                    <div>
                        <div class="flex justify-between text-xs font-bold text-navy mb-1">
                            <span>Durasi Tidur Semalam</span>
                            <span class="text-aurea font-extrabold">{{ $sleepDuration }} Jam</span>
                        </div>
                        <input type="range" min="3" max="12" step="0.5" wire:model.live="sleepDuration"
                               class="w-full accent-navy cursor-pointer">
                        <span class="text-[10px] text-gray-400">Rekomendasi remaja: 8 - 10 jam</span>
                    </div>

                    <!-- Physical Activity -->
                    <div>
                        <div class="flex justify-between text-xs font-bold text-navy mb-1">
                            <span>Aktivitas Fisik Hari Ini</span>
                            <span class="text-aurea font-extrabold">{{ $physicalActivityDuration }} Menit</span>
                        </div>
                        <input type="range" min="0" max="180" step="5" wire:model.live="physicalActivityDuration"
                               class="w-full accent-navy cursor-pointer">
                        <span class="text-[10px] text-gray-400">Pedoman WHO: minimal 60 menit sehari</span>
                    </div>

                    <!-- Screen Time -->
                    <div>
                        <div class="flex justify-between text-xs font-bold text-navy mb-1">
                            <span>Screen Time Rekreasional</span>
                            <span class="text-aurea font-extrabold">{{ $screenTimeDuration }} Jam</span>
                        </div>
                        <input type="range" min="0.5" max="14" step="0.5" wire:model.live="screenTimeDuration"
                               class="w-full accent-navy cursor-pointer">
                        <span class="text-[10px] text-gray-400">Batas wajar: < 4 jam sehari</span>
                    </div>

                    <!-- WHO-5 Mental Well-being -->
                    <div>
                        <div class="flex justify-between text-xs font-bold text-navy mb-1">
                            <span>Indikator Mental Well-being (WHO-5)</span>
                            <span class="text-aurea font-extrabold">{{ $who5Score }} / 100</span>
                        </div>
                        <input type="range" min="0" max="100" step="5" wire:model.live="who5Score"
                               class="w-full accent-navy cursor-pointer">
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-bold text-navy mb-1">Catatan Tambahan (Jurnal Singkat)</label>
                        <textarea wire:model="note" rows="2" placeholder="Apa yang kamu rasakan hari ini?"
                                  class="w-full bg-gray-50 rounded-2xl p-3 text-xs border border-gray-200 focus:outline-none focus:ring-2 focus:ring-navy"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" wire:click="closeDetailedModal"
                            class="px-4 py-2 rounded-full text-xs font-semibold text-gray-500 hover:text-navy">
                        Batal
                    </button>
                    <button type="button" wire:click="saveDetailedCheckIn"
                            class="px-6 py-2.5 rounded-full text-xs font-bold bg-navy text-white shadow-md hover:bg-opacity-95 transition">
                        Hitung Index & Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>