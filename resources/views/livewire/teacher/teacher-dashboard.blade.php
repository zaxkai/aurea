<div class="space-y-8 max-w-6xl mx-auto pb-12 pt-2">
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold rounded-2xl p-4 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span>🎉</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold text-xs cursor-pointer">✕</button>
        </div>
    @endif

    @if (session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs sm:text-sm font-semibold rounded-2xl p-4 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span>ℹ️</span>
                <span>{{ session('info') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-blue-600 hover:text-blue-900 font-bold text-xs cursor-pointer">✕</button>
        </div>
    @endif

    <!-- Header with Title, Classroom Filter, & Create Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#000F2E] tracking-tight">
                    Hello, {{ auth()->user()->first_name ?? 'Teacher' }}
                </h1>
                @if (auth()->user()->isPremium())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs">
                        <span>✨</span>
                        <span>Paket B2B Sekolah Aktif</span>
                    </span>
                    <button type="button"
                            wire:click="resetDemoSubscription"
                            title="Kembalikan ke Akun Free untuk simulasi presentasi paywall"
                            class="text-[11px] font-semibold text-gray-400 hover:text-red-500 underline transition-colors cursor-pointer">
                        (Reset ke Free)
                    </button>
                @else
                    <button type="button"
                            wire:click="openSubscriptionModal"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-300 transition-colors shadow-2xs cursor-pointer">
                        <span>🔒</span>
                        <span>Akun Free • Berlangganan B2B</span>
                    </button>
                @endif
            </div>
            <p class="text-gray-500 font-medium text-sm mt-1">
                See how students are feeling at a glance
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if ($classrooms->isNotEmpty())
                <div class="relative">
                    <select wire:model.live="selectedClassroomId"
                            class="bg-white border border-gray-200 text-[#000F2E] font-semibold text-xs sm:text-sm rounded-2xl py-2.5 px-4 pr-9 focus:ring-2 focus:ring-[#000F2E] focus:outline-none shadow-sm cursor-pointer">
                        <option value="">All Classrooms ({{ $classrooms->sum('students_count') }} Students)</option>
                        @foreach ($classrooms as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }} ({{ $cls->students_count }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="button"
                    wire:click="$set('showCreateModal', true)"
                    class="inline-flex items-center gap-2 bg-[#000F2E] text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-2xl shadow-md hover:bg-[#001744] active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>New Classroom</span>
            </button>
        </div>
    </div>

    <!-- Active Classrooms Banner / Code Sharing (if exists) -->
    @if ($classrooms->isNotEmpty())
        <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                    🏫
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#000F2E]">Your Classroom Codes for Students</h3>
                    <p class="text-xs text-gray-500">Share these codes with your students so they can connect with your dashboard safely.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2" x-data="{
                copiedId: null,
                copy(id, code) {
                    const mark = () => {
                        this.copiedId = id;
                        setTimeout(() => {
                            if (this.copiedId === id) this.copiedId = null;
                        }, 2000);
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(code).then(mark).catch(() => this.fallback(code, mark));
                    } else {
                        this.fallback(code, mark);
                    }
                },
                fallback(text, cb) {
                    try {
                        const el = document.createElement('textarea');
                        el.value = text;
                        el.setAttribute('readonly', '');
                        el.style.position = 'fixed';
                        el.style.left = '-9999px';
                        el.style.top = '0';
                        document.body.appendChild(el);
                        el.focus();
                        el.select();
                        const ok = document.execCommand('copy');
                        document.body.removeChild(el);
                        if (ok && cb) cb();
                    } catch (e) {
                        console.error('Failed to copy classroom code:', e);
                    }
                }
            }">
                @foreach ($classrooms as $cls)
                    <button type="button"
                            wire:key="cls-copy-{{ $cls->id }}"
                            @click="copy({{ $cls->id }}, '{{ $cls->code }}')"
                            :class="copiedId === {{ $cls->id }} ? 'border-emerald-400 bg-emerald-50/60 text-emerald-800' : 'border-blue-200/80 bg-white hover:border-blue-400 text-[#000F2E]'"
                            class="inline-flex items-center gap-2 border px-3 py-1.5 rounded-xl shadow-xs text-xs font-semibold transition-all cursor-pointer">
                        <span>{{ $cls->name }}:</span>
                        <span class="font-mono font-bold tracking-wider text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">{{ $cls->code }}</span>
                        <span x-show="copiedId === {{ $cls->id }}" x-cloak class="text-emerald-600 font-bold text-[11px] inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Copied!
                        </span>
                        <svg x-show="copiedId !== {{ $cls->id }}" class="w-3.5 h-3.5 text-gray-400 hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Top 3 Stat Cards (Matching the user's Mockup) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Students Card (Always Accessible) -->
        <div class="bg-[#000F2E] text-white rounded-[28px] p-7 flex flex-col items-center justify-center min-h-[190px] shadow-lg shadow-navy/10 relative overflow-hidden group">
            <div class="flex items-center gap-3.5">
                <svg class="w-10 h-10 text-[#4CC9F0]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                </svg>
                <span class="text-5xl font-black tracking-tight">{{ $studentsCount }}</span>
            </div>
            <div class="text-sm font-semibold text-gray-300 mt-3 tracking-wide">
                Students
            </div>
        </div>

        <!-- Wellbeing Score Card -->
        <div class="bg-[#000F2E] text-white rounded-[28px] p-7 flex flex-col items-center justify-center min-h-[190px] shadow-lg shadow-navy/10 relative overflow-hidden group">
            <div class="text-5xl font-black tracking-tight {{ ! auth()->user()->isPremium() ? 'filter blur-[5px] select-none opacity-60' : '' }}">
                {{ $wellbeingScore }}%
            </div>
            <div class="text-sm font-semibold text-gray-300 mt-3 tracking-wide flex items-center gap-1.5">
                <span>Wellbeing Score</span>
                @if (! auth()->user()->isPremium())
                    <span class="text-amber-400 text-xs font-bold" title="Fitur Premium B2B Sekolah">🔒</span>
                @endif
            </div>
        </div>

        <!-- Need Attention Card -->
        <div class="bg-[#000F2E] text-white rounded-[28px] p-7 flex flex-col items-center justify-center min-h-[190px] shadow-lg shadow-navy/10 relative overflow-hidden group">
            <div class="flex items-center gap-3.5 {{ ! auth()->user()->isPremium() ? 'filter blur-[5px] select-none opacity-60' : '' }}">
                <svg class="w-9 h-9 text-[#F4B400]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" />
                </svg>
                <span class="text-5xl font-black tracking-tight">{{ $needAttentionCount }}</span>
            </div>
            <div class="text-sm font-semibold text-gray-300 mt-3 tracking-wide flex items-center gap-1.5">
                <span>Need Attention</span>
                @if (! auth()->user()->isPremium())
                    <span class="text-amber-400 text-xs font-bold" title="Fitur Premium B2B Sekolah">🔒</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Mood Overview Section (Matching Mockup with B2B Paywall) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-[#000F2E] tracking-tight">Mood Overview</h2>
                <p class="text-gray-500 font-medium text-xs sm:text-sm">An overview of students' reported moods this week</p>
            </div>
            @if (! auth()->user()->isPremium())
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1.5 shadow-2xs">
                    <span>🔒</span>
                    <span>Layanan Berbayar B2B Sekolah</span>
                </span>
            @endif
        </div>

        <div class="relative rounded-[28px] overflow-hidden">
            @if (! auth()->user()->isPremium())
                <!-- Paywall Overlay Card -->
                <div class="absolute inset-0 z-20 bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
                    <div class="max-w-lg w-full bg-white/95 rounded-[28px] p-6 sm:p-7 shadow-2xl text-center space-y-4 border border-amber-300/80">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-[11px] font-black uppercase tracking-wider">
                            <span>🏫</span>
                            <span>B2B (Sekolah) • Layanan Berbayar</span>
                        </div>

                        <div class="space-y-1">
                            <h3 class="text-xl sm:text-2xl font-black text-[#000F2E] tracking-tight">
                                Deteksi Dini & Analitik Guru BK
                            </h3>
                            <p class="text-xs text-gray-600 leading-relaxed max-w-sm mx-auto">
                                Buka analitik tren burnout siswa per angkatan secara kolektif tanpa melanggar privasi obrolan individu.
                            </p>
                        </div>

                        <div class="text-left text-xs space-y-1.5 bg-gray-50 p-3.5 rounded-2xl border border-gray-100 text-gray-700">
                            <div class="flex items-start gap-2">
                                <span class="text-amber-500 font-bold">✦</span>
                                <span><strong>Sistem Berlangganan:</strong> Sekolah membayar biaya subscription per semester/tahun.</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-amber-500 font-bold">✦</span>
                                <span><strong>Akses Eksklusif:</strong> Membuka dashboard analitik khusus untuk Guru BK.</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-amber-500 font-bold">✦</span>
                                <span><strong>Deteksi Dini:</strong> Memantau tren burnout siswa tanpa melanggar privasi obrolan individu.</span>
                            </div>
                        </div>

                        <button type="button"
                                wire:click="openSubscriptionModal"
                                class="w-full inline-flex items-center justify-center gap-2 bg-[#000F2E] text-white font-bold text-xs sm:text-sm py-3 px-5 rounded-2xl shadow-lg hover:bg-[#001744] active:scale-95 transition-all cursor-pointer">
                            <span>💳</span>
                            <span>Buka Paket Berlangganan (Simulasi Demo)</span>
                        </button>
                    </div>
                </div>
            @endif

            <div class="bg-[#e4ebfc] rounded-[28px] p-6 sm:p-8 space-y-4 shadow-sm {{ ! auth()->user()->isPremium() ? 'filter blur-[4px] select-none pointer-events-none' : '' }}">
                @foreach ($moodStats as $key => $mood)
                    <div wire:key="mood-stat-{{ $key }}" class="flex items-center gap-4 sm:gap-6">
                        <!-- Mood Label with Mascot -->
                        <div class="flex items-center gap-2.5 w-28 sm:w-32 shrink-0">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $mood['bg_color'] }} p-1 shadow-2xs flex items-center justify-center shrink-0 border border-black/5">
                                <img src="{{ asset('images/journal/' . $mood['mascot']) }}" alt="{{ $mood['label'] }}" class="w-full h-full object-contain">
                            </div>
                            <span class="font-bold text-sm sm:text-base text-[#000F2E]">{{ $mood['label'] }}</span>
                        </div>

                        <!-- Progress Bar Container -->
                        <div class="flex-1 bg-white h-7 sm:h-8 rounded-full overflow-hidden p-1 shadow-inner relative">
                            <div class="bg-[#000F2E] h-full rounded-full transition-all duration-700 ease-out"
                                 style="width: {{ $mood['pct'] > 0 ? max(4, $mood['pct']) : 0 }}%"></div>
                        </div>

                        <!-- Percentage -->
                        <div class="w-12 sm:w-14 text-right font-bold text-sm sm:text-base text-[#000F2E] shrink-0">
                            {{ $mood['pct'] }}%
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal: Create New Classroom -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto"
             x-data
             @keydown.escape.window="$wire.set('showCreateModal', false)">
            <!-- Full-screen Backdrop -->
            <div class="fixed inset-0 bg-navy/50 backdrop-blur-xs transition-opacity" wire:click="$set('showCreateModal', false)" aria-hidden="true"></div>

            <div class="relative flex min-h-full items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-6 relative border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-[#000F2E]">Create New Classroom</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Students will use the generated code to join.</p>
                    </div>
                    <button type="button"
                            wire:click="$set('showCreateModal', false)"
                            class="w-8 h-8 rounded-full text-gray-400 hover:text-navy hover:bg-gray-100 flex items-center justify-center transition-colors">
                        ✕
                    </button>
                </div>

                <form wire:submit="createClassroom" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Classroom Name *</label>
                        <input type="text"
                               wire:model="newClassName"
                               placeholder="e.g. Kelas 10 IPA 1 / Class 10-A"
                               required
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]" />
                        @error('newClassName') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">School Name (Optional)</label>
                        <input type="text"
                               wire:model="newSchoolName"
                               placeholder="e.g. SMA Negeri 1 Jakarta"
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]" />
                        @error('newSchoolName') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Description (Optional)</label>
                        <textarea wire:model="newClassDescription"
                                  rows="2"
                                  placeholder="Notes for this classroom..."
                                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button"
                                wire:click="$set('showCreateModal', false)"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#000F2E] text-white hover:bg-[#001b3d] shadow-md transition-all">
                            Generate Classroom & Code
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    @endif

    <!-- Modal: B2B School Subscription (Demo Simulation) -->
    @if ($showSubscriptionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto"
             x-data
             @keydown.escape.window="$wire.set('showSubscriptionModal', false)">
            <!-- Full-screen Backdrop -->
            <div class="fixed inset-0 bg-[#000F2E]/60 backdrop-blur-xs transition-opacity" wire:click="closeSubscriptionModal" aria-hidden="true"></div>

            <div class="relative flex min-h-full items-center justify-center p-4">
                <div class="bg-white rounded-[32px] max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6 relative border border-gray-100">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[11px] font-black uppercase tracking-wider mb-1.5 border border-amber-200">
                                Kemitraan Sekolah (B2B)
                            </span>
                            <h3 class="text-2xl font-black text-[#000F2E] tracking-tight">Paket Berlangganan Guru BK</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Pilih paket lisensi kemitraan sekolah untuk akses dashboard analitik lengkap.</p>
                        </div>
                        <button type="button"
                                wire:click="closeSubscriptionModal"
                                class="w-8 h-8 rounded-full text-gray-400 hover:text-navy hover:bg-gray-100 flex items-center justify-center transition-colors cursor-pointer">
                            ✕
                        </button>
                    </div>

                    <!-- Plan Selection -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="button"
                                wire:click="selectPlan('semester')"
                                class="p-4 rounded-2xl border-2 text-left transition-all cursor-pointer {{ $selectedPlan === 'semester' ? 'border-[#000F2E] bg-blue-50/50 shadow-sm' : 'border-gray-200 hover:border-gray-300' }}">
                            <div class="text-xs font-bold text-gray-500">Paket Semester</div>
                            <div class="text-lg font-black text-[#000F2E] mt-1">Rp 1.500.000</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">per semester (6 bulan)</div>
                        </button>

                        <button type="button"
                                wire:click="selectPlan('yearly')"
                                class="p-4 rounded-2xl border-2 text-left transition-all relative overflow-hidden cursor-pointer {{ $selectedPlan === 'yearly' ? 'border-[#000F2E] bg-blue-50/50 shadow-sm' : 'border-gray-200 hover:border-gray-300' }}">
                            <span class="absolute top-2 right-2 text-[10px] font-black bg-emerald-500 text-white px-2 py-0.5 rounded-full">Hemat 16%</span>
                            <div class="text-xs font-bold text-gray-500">Paket Tahunan</div>
                            <div class="text-lg font-black text-[#000F2E] mt-1">Rp 2.500.000</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">per tahun (12 bulan)</div>
                        </button>
                    </div>

                    <!-- Features Included -->
                    <div class="bg-gray-50 rounded-2xl p-4 text-xs space-y-2 text-gray-700 border border-gray-100">
                        <div class="font-bold text-[#000F2E] mb-1">Akses Eksklusif yang Didapatkan:</div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Dashboard Analitik Kesejahteraan Siswa & Guru BK</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Deteksi Dini Tren Burnout Siswa per Angkatan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Indikator Siswa yang Memerlukan Perhatian (Need Attention)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Perlindungan Privasi: Tanpa melanggar obrolan pribadi siswa</span>
                        </div>
                    </div>

                    <!-- Demo Simulation Disclaimer (Explicitly highlighting paid model in demo stage) -->
                    <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 text-xs text-amber-900 space-y-1.5">
                        <div class="font-bold flex items-center gap-1.5 text-amber-950">
                            <span>💳</span>
                            <span>Layanan Berlangganan Berbayar (Tahap Uji Coba Demo)</span>
                        </div>
                        <p class="leading-relaxed text-[11px]">
                            Fitur ini merupakan layanan berbayar kemitraan B2B untuk sekolah. Sistem transaksi pembayaran saat ini masih dalam <strong>tahap demo / uji coba inovasi</strong>, sehingga Anda dapat melakukan <strong>simulasi pembayaran</strong> untuk mengaktifkan seluruh fitur analitik tanpa pemotongan biaya riil.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-1">
                        <button type="button"
                                wire:click="closeSubscriptionModal"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="activateDemoSubscription"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#000F2E] text-white hover:bg-[#001744] shadow-md transition-all active:scale-95 cursor-pointer">
                            <span>💳</span>
                            <span>Simulasikan Pembayaran & Aktifkan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
