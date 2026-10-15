<x-app-layout>
    @php
        $treeStages = [
            ['name' => 'Seed', 'description' => 'A small step can start massive growth.'],
            ['name' => 'Sprout', 'description' => 'The habit of completing tasks begins to take root.'],
            ['name' => 'Sapling', 'description' => 'Your consistency starts growing new branches.'],
            ['name' => 'Blooming', 'description' => 'Your dedication shows in a thriving tree.'],
            ['name' => 'Lush', 'description' => 'See the results of all the goals you have accomplished.'],
        ];
        $currentStage = $treeStages[$treeStage];
        $stageMilestones = [0, 1, 3, 6, 10];
        $nextStageTaskCount = $stageMilestones[$treeStage + 1] ?? null;
        $tasksUntilNextStage = $nextStageTaskCount === null
            ? 0
            : max(0, $nextStageTaskCount - $completedGrowthItemsCount);
        $stageProgress = $nextStageTaskCount === null
            ? 100
            : (int) round((($completedGrowthItemsCount - $stageMilestones[$treeStage]) / ($nextStageTaskCount - $stageMilestones[$treeStage])) * 100);
    @endphp

    <div x-data="{ showSkinModal: false }">
        @if (session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold rounded-2xl p-4 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span>🎉</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold text-xs cursor-pointer">✕</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold rounded-2xl p-4 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold text-xs cursor-pointer">✕</button>
            </div>
        @endif

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-navy tracking-tight">Habit Growth Tree</h1>
                <p class="mt-1 text-sm text-gray-500">Setiap kebiasaan dan tugas yang kamu selesaikan membantu pohon ketahanan dirimu bertumbuh dan mekar.</p>
            </div>

            <!-- Kostumisasi Skin Button in Header -->
            <button type="button"
                    @click="showSkinModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white border border-amber-300 hover:bg-amber-50/60 text-xs font-bold text-navy shadow-xs transition-all active:scale-95 self-start sm:self-auto cursor-pointer">
                <span>🎨</span>
                <span>Ganti Skin Pohon</span>
                @if (auth()->user()->isPremium())
                    <span class="rounded-full bg-emerald-100 text-emerald-800 px-2 py-0.5 text-[10px] font-black">PRO</span>
                @else
                    <span class="rounded-full bg-amber-100 text-amber-900 px-2 py-0.5 text-[10px] font-black">🔒 PRO</span>
                @endif
            </button>
        </div>

        <div class="grid max-w-5xl grid-cols-1 gap-7 md:grid-cols-2">
            <section class="flex flex-col items-center justify-center rounded-3xl border border-gray-100 bg-white p-6 text-center shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center justify-center gap-2.5">
                    <h2 class="text-lg font-bold text-navy">Personal Resilience Tree</h2>
                    <span class="rounded-full bg-aurea/20 px-3 py-1 text-xs font-bold text-navy">{{ $currentStage['name'] }}</span>
                    @if (isset($availableSkins[$currentSkin]) && $currentSkin !== 'default')
                        <span class="rounded-full bg-pink-100 text-pink-800 border border-pink-200 px-2.5 py-0.5 text-[11px] font-bold inline-flex items-center gap-1 shadow-2xs">
                            <span>{{ $availableSkins[$currentSkin]['icon'] ?? '✨' }}</span>
                            <span>{{ $availableSkins[$currentSkin]['name'] }}</span>
                        </span>
                    @endif
                </div>

                <x-growth-tree :stage="$treeStage" />

                <!-- Skin Button below Tree -->
                <button type="button"
                        @click="showSkinModal = true"
                        class="mt-2 inline-flex items-center justify-center gap-2 px-4 py-1.5 rounded-full bg-gray-50 hover:bg-amber-50 border border-gray-200 hover:border-amber-300 text-xs font-bold text-gray-700 hover:text-navy transition-all active:scale-95 cursor-pointer">
                    <span>🎨</span>
                    <span>Tema: <strong>{{ $availableSkins[$currentSkin]['name'] ?? 'Klasik' }}</strong></span>
                    <span class="text-gray-400 font-normal underline text-[11px]">(Ubah Skin)</span>
                </button>

                <p class="mt-3 text-sm text-gray-500">{{ $currentStage['description'] }}</p>
                <p class="mt-4 text-2xl font-extrabold text-navy">{{ $completedGrowthItemsCount }} <span class="text-sm font-semibold text-gray-500">targets completed</span></p>

                @if ($nextStageTaskCount !== null)
                    <div class="mt-4 w-full max-w-sm">
                        <div class="mb-2 flex items-center justify-between gap-3 text-xs font-semibold text-gray-500">
                            <span>{{ $tasksUntilNextStage }} {{ \Illuminate\Support\Str::plural('task', $tasksUntilNextStage) }} to {{ $treeStages[$treeStage + 1]['name'] }}</span>
                            <span>{{ $stageProgress }}%</span>
                        </div>
                        <div
                            class="h-2.5 overflow-hidden rounded-full bg-gray-100"
                            role="progressbar"
                            aria-label="Progress to next tree stage"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="{{ $stageProgress }}"
                        >
                            <div class="h-full rounded-full bg-aurea transition-all duration-500" style="width: {{ $stageProgress }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="mt-4 rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">Your tree is flourishing. Keep up your consistency!</p>
                @endif
            </section>

            <section class="flex flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <div>
                    <h2 class="mb-2 text-lg font-bold text-navy">Growth Journey</h2>
                    <p class="mb-5 text-sm text-gray-500">Every completed daily habit and finished task unlocks a new stage in your tree's journey.</p>

                    <ol class="space-y-3">
                        @foreach ($treeStages as $index => $stage)
                            <li class="flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $index <= $treeStage ? 'border-aurea/50 bg-aurea/10' : 'border-gray-100 bg-gray-50' }}">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $index <= $treeStage ? 'bg-navy text-white' : 'bg-white text-gray-400' }}">
                                    {{ $index + 1 }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-navy">{{ $stage['name'] }}</span>
                                    <span class="block text-xs text-gray-500">
                                        @if ($index === 0)
                                            Start here
                                        @else
                                            {{ $stageMilestones[$index] }} targets completed
                                        @endif
                                    </span>
                                </span>
                                @if ($index <= $treeStage)
                                    <span class="text-xs font-semibold text-emerald-700">Achieved</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>

                    <h3 class="mb-3 mt-7 text-sm font-bold text-navy">Active Habits</h3>
                    <div class="space-y-3">
                        @forelse (auth()->user()->habits as $habit)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-3.5">
                                <span class="text-xs font-bold text-navy">{{ $habit->name }}</span>
                                <span class="shrink-0 rounded-full bg-navy px-2.5 py-1 text-xs font-semibold text-white">{{ $habit->target_value }} {{ $habit->unit }}</span>
                            </div>
                        @empty
                            <p class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-500">No active habits yet.</p>
                        @endforelse
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" class="mt-6 w-full rounded-full bg-navy py-3 text-center text-xs font-bold text-white transition hover:bg-navy/90">
                    Back to Dashboard
                </a>
            </section>
        </div>

        <!-- Modal: Kostumisasi Skin Pohon -->
        <div x-show="showSkinModal"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="showSkinModal = false">
            <!-- Full Backdrop -->
            <div class="fixed inset-0 bg-[#000F2E]/60 backdrop-blur-xs transition-opacity" @click="showSkinModal = false"></div>

            <div class="relative flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-3xl rounded-[32px] bg-white p-6 sm:p-8 shadow-2xl border border-gray-100 space-y-6 my-8">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 uppercase tracking-wider mb-2">
                                <span>🎨</span>
                                <span>Koleksi Skin Pohon Eksklusif</span>
                            </div>
                            <h3 class="text-2xl font-black text-[#000F2E] tracking-tight">Kostumisasi Tampilan Pohon</h3>
                            <p class="text-xs text-gray-500 mt-1">Pilih tema pohon favoritmu untuk merefleksikan proses bertumbuh dan konsistensi kebiasaanmu.</p>
                        </div>
                        <button type="button"
                                @click="showSkinModal = false"
                                class="w-9 h-9 rounded-full text-gray-400 hover:text-navy hover:bg-gray-100 flex items-center justify-center transition-colors cursor-pointer text-lg font-bold">
                            ✕
                        </button>
                    </div>

                    @if (! auth()->user()->isPremium())
                        <!-- Promo Banner for Free Users -->
                        <div class="bg-gradient-to-r from-amber-50/90 via-white to-blue-50/90 border border-amber-300 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
                            <div class="text-xs text-gray-700 leading-relaxed text-center sm:text-left">
                                <span class="font-bold text-[#000F2E] flex items-center gap-1.5 justify-center sm:justify-start">
                                    <span>⭐</span>
                                    <span>Fitur Eksklusif Murid Premium</span>
                                </span>
                                <p class="text-gray-500 mt-0.5">Aktifkan Aurea Premium untuk membuka kebebasan memakai semua skin pohon (Sakura, Cemara, Musim Gugur, Natal, dan Apel Buah).</p>
                            </div>
                            <a href="{{ route('premium') }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#000F2E] text-white font-bold text-xs hover:bg-[#001744] transition-all shadow-md shrink-0">
                                <span>⭐</span>
                                <span>Buka Semua Skin (Premium)</span>
                            </a>
                        </div>
                    @else
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 flex items-center gap-2.5 text-xs text-emerald-900">
                            <span class="text-base">✨</span>
                            <span><strong>Status Premium Aktif:</strong> Kamu bebas memilih dan berganti skin pohon kapan saja!</span>
                        </div>
                    @endif

                    <!-- Skin Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($availableSkins as $key => $skin)
                            @php
                                $isActive = $currentSkin === $key;
                                $isLocked = $skin['is_premium'] && ! auth()->user()->isPremium();
                                $previewImg = $key === 'default'
                                    ? asset('images/habit-growth-tree/pohon4.png')
                                    : asset("images/habit-growth-tree/skins/{$key}/preview.png");
                            @endphp
                            <div class="relative rounded-2xl border-2 p-4 flex flex-col justify-between transition-all bg-white {{ $isActive ? 'border-[#000F2E] ring-2 ring-[#000F2E]/20 shadow-md bg-blue-50/20' : 'border-gray-200 hover:border-gray-300 shadow-2xs' }}">
                                @if ($isActive)
                                    <span class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-[#000F2E] text-white shadow-2xs">
                                        Aktif
                                    </span>
                                @elseif ($isLocked)
                                    <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                        🔒 PRO
                                    </span>
                                @endif

                                <div>
                                    <!-- Tree Preview Thumbnail -->
                                    <div class="h-32 w-full flex items-center justify-center p-2 mb-3 bg-gray-50/70 rounded-xl overflow-hidden {{ $isLocked ? 'filter grayscale-[30%] opacity-85' : '' }}">
                                        <img src="{{ $previewImg }}" alt="{{ $skin['name'] }}" class="h-full w-full object-contain">
                                    </div>

                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 font-bold text-sm text-[#000F2E]">
                                            <span>{{ $skin['icon'] }}</span>
                                            <span>{{ $skin['name'] }}</span>
                                        </div>
                                        <p class="text-[11px] text-gray-500 leading-snug">{{ $skin['desc'] }}</p>
                                    </div>
                                </div>

                                <div class="pt-4 mt-auto">
                                    @if ($isActive)
                                        <button type="button" disabled class="w-full py-2 px-3 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs text-center cursor-default">
                                            ✓ Sedang Digunakan
                                        </button>
                                    @elseif ($isLocked)
                                        <a href="{{ route('premium') }}"
                                           class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 font-bold text-xs text-center transition-colors">
                                            <span>🔒</span>
                                            <span>Buka dengan Premium</span>
                                        </a>
                                    @else
                                        <form method="POST" action="{{ route('habit-growth-tree.skin') }}">
                                            @csrf
                                            <input type="hidden" name="skin" value="{{ $key }}">
                                            <button type="submit"
                                                    class="w-full py-2 px-3 rounded-xl bg-[#000F2E] hover:bg-[#001744] text-white font-bold text-xs text-center transition-all active:scale-95 shadow-xs cursor-pointer">
                                                Gunakan Skin Ini
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="button"
                                @click="showSkinModal = false"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
