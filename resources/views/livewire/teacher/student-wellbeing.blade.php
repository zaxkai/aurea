<div class="space-y-6 max-w-6xl mx-auto pb-12 pt-2">
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

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-3xl font-extrabold text-[#000F2E] tracking-tight">Student Wellbeing</h1>
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
                Monitor and support students in your enrolled classrooms
            </p>
        </div>

        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 text-xs font-bold text-gray-600 hover:text-[#000F2E] bg-white border border-gray-200 px-4 py-2.5 rounded-2xl shadow-xs transition-colors self-start sm:self-auto cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <!-- Privacy Banner -->
    <div class="bg-emerald-50/70 border border-emerald-200/60 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold shrink-0 text-sm">
            🛡️
        </div>
        <div class="text-xs text-emerald-900 leading-relaxed">
            <span class="font-bold">Student Privacy Boundary Active:</span>
            As a teacher, you have access to student emotional check-in trends and aggregated wellbeing scores for guidance. Personal journal entries and AI chat conversations are strictly confidential and inaccessible to protect student trust.
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <div class="flex-1 max-w-md relative">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search student by name or email..."
                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-xs sm:text-sm font-medium text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]" />
        </div>

        <div class="flex items-center gap-2">
            <!-- Classroom filter -->
            <select wire:model.live="selectedClassroomId"
                    class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]">
                <option value="">All Classrooms</option>
                @foreach ($classrooms as $cls)
                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                @endforeach
            </select>

            <!-- Status filter -->
            <select wire:model.live="statusFilter"
                    class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]">
                <option value="all">All Status</option>
                <option value="attention">⚠️ Need Attention</option>
                <option value="healthy">🌱 Healthy / On Track</option>
            </select>
        </div>
    </div>

    <!-- Students Table / List Container with Paywall Overlay -->
    <div class="relative rounded-3xl overflow-hidden">
        @if (! auth()->user()->isPremium())
            <!-- Paywall Overlay -->
            <div class="absolute inset-0 z-20 bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
                <div class="max-w-md w-full bg-white/95 rounded-[28px] p-6 sm:p-7 shadow-2xl text-center space-y-4 border border-amber-300/80">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-[11px] font-black uppercase tracking-wider">
                        <span>🏫</span>
                        <span>B2B (Sekolah) • Layanan Berbayar</span>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-xl sm:text-2xl font-black text-[#000F2E] tracking-tight">
                            Deteksi Dini Siswa Terkunci
                        </h3>
                        <p class="text-xs text-gray-600 leading-relaxed max-w-sm mx-auto">
                            Aktifkan paket kemitraan sekolah untuk memantau status kesejahteraan siswa secara individual dan mendeteksi siswa yang memerlukan pendampingan segera.
                        </p>
                    </div>

                    <div class="text-left text-xs space-y-1.5 bg-gray-50 p-3.5 rounded-2xl border border-gray-100 text-gray-700">
                        <div class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">✦</span>
                            <span><strong>Sistem Berlangganan:</strong> Lisensi sekolah per semester/tahun.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">✦</span>
                            <span><strong>Deteksi Dini:</strong> Filter status <em>Need Attention</em> untuk intervensi cepat Guru BK.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">✦</span>
                            <span><strong>Privasi Aman:</strong> Menampilkan tren emosi tanpa mengakses obrolan pribadi.</span>
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

        <div class="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm {{ ! auth()->user()->isPremium() ? 'filter blur-[4px] select-none pointer-events-none' : '' }}">
            @if ($students->isEmpty())
                <div class="py-16 text-center space-y-3">
                    <div class="text-4xl">📚</div>
                    <h3 class="text-base font-bold text-[#000F2E]">No Students Found</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">
                        @if ($classrooms->isEmpty())
                            You have not created any classrooms yet. Create a classroom and share the code with students.
                        @else
                            No students match your filter or have joined this classroom yet.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-gray-50/70 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Student</th>
                                <th class="py-3.5 px-4">Classroom</th>
                                <th class="py-3.5 px-4">Latest Mood</th>
                                <th class="py-3.5 px-4">Wellbeing Score</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-6 text-right">Last Check-in</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($students as $student)
                                <tr wire:key="student-row-{{ $student['id'] }}" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6 flex items-center gap-3">
                                        <img src="{{ $student['avatar_url'] }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0 ring-1 ring-gray-100">
                                        <div>
                                            <div class="font-bold text-[#000F2E]">{{ $student['name'] }}</div>
                                            <div class="text-[11px] text-gray-400 font-medium">{{ $student['email'] }}</div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 font-semibold text-gray-700">
                                        <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-lg text-xs font-bold">
                                            {{ $student['classroom_name'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 font-bold text-[#000F2E]">
                                        @if ($student['latest_mood_mascot'])
                                            <div class="inline-flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full {{ $student['latest_mood_color'] }} p-0.5 flex items-center justify-center shrink-0 shadow-2xs border border-black/5">
                                                    <img src="{{ asset('images/journal/' . $student['latest_mood_mascot']) }}" alt="{{ $student['latest_mood_label'] }}" class="w-full h-full object-contain">
                                                </div>
                                                <span>{{ $student['latest_mood_label'] }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400 font-medium text-xs">— {{ $student['latest_mood_label'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-100 h-2 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $student['wellbeing_score'] < 60 ? 'bg-amber-500' : 'bg-emerald-500' }}"
                                                     style="width: {{ $student['wellbeing_score'] }}%"></div>
                                            </div>
                                            <span class="font-bold text-xs {{ $student['wellbeing_score'] < 60 ? 'text-amber-600' : 'text-[#000F2E]' }}">
                                                {{ $student['wellbeing_score'] }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if ($student['needs_attention'])
                                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200/80 px-2.5 py-1 rounded-xl text-[11px] font-bold">
                                                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Need Attention</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-2.5 py-1 rounded-xl text-[11px] font-bold">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Healthy</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right text-gray-400 font-medium text-xs">
                                        {{ $student['last_active'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

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
