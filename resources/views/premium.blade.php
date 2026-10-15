<x-app-layout>
    <div class="relative isolate flex min-h-full items-center justify-center overflow-y-auto bg-[linear-gradient(180deg,#f2f3f8_0%,#f2f3f8_42%,#b8f3f1_76%,#56dfdc_100%)] px-4 py-8 sm:px-8">
        <section class="relative flex w-full max-w-3xl flex-col items-center gap-6 sm:gap-7 my-auto">
            <img src="{{ asset('images/logo_aurea.png') }}" alt="AUREA" class="h-10 w-32 object-contain">

            @if (auth()->user()?->isTeacher())
                {{-- ==================== TEACHER / B2B SEKOLAH ==================== --}}
                <div class="text-center">
                    <h1 class="text-3xl font-extrabold leading-tight text-navy sm:text-5xl">
                        AUREA <span class="text-[#efb323]">for School</span>
                    </h1>
                    <p class="mt-2 text-sm text-navy sm:text-base">Kemitraan Sekolah & Deteksi Dini Burnout Siswa Khusus Guru BK.</p>
                </div>

                <article class="w-full rounded-[26px] border-2 border-aurea bg-gradient-to-br from-[#bff8f7] via-[#dcfbfa] to-[#f8ffff] p-5 shadow-[0_24px_70px_-38px_rgba(0,15,46,0.45)] sm:rounded-[30px] sm:p-8 space-y-6">
                    <div class="grid items-center gap-5 sm:grid-cols-[minmax(0,1fr)_150px] sm:gap-8">
                        <div>
                            <div class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-black uppercase tracking-wider mb-2 border border-amber-300">
                                B2B (Sekolah) • Layanan Berbayar
                            </div>
                            <h2 class="text-xl font-bold text-navy sm:text-2xl">Fitur Eksklusif Guru BK</h2>
                            <ul class="mt-3 space-y-2.5 text-xs font-medium text-[#008e95] sm:text-sm">
                                <li class="flex gap-2">
                                    <span class="text-amber-500 font-bold" aria-hidden="true">✦</span>
                                    <span><strong>Sistem Berlangganan:</strong> Sekolah membayar biaya subscription per semester/tahun.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-amber-500 font-bold" aria-hidden="true">✦</span>
                                    <span><strong>Akses Eksklusif:</strong> Membuka dashboard analitik khusus Guru BK.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-amber-500 font-bold" aria-hidden="true">✦</span>
                                    <span><strong>Deteksi Dini:</strong> Memantau tren burnout siswa per angkatan secara kolektif tanpa melanggar privasi obrolan individu.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-amber-500 font-bold" aria-hidden="true">✦</span>
                                    <span><strong>Manajemen Kelas:</strong> Bebas buat banyak kelas dan pantau siswa terhubung.</span>
                                </li>
                            </ul>
                        </div>
                        <img src="{{ asset('images/maskot_aurea.png') }}" alt="Aurea Mascot" class="mx-auto h-36 w-20 max-w-full object-contain sm:h-44 sm:w-24">
                    </div>

                    <!-- Plan Preview -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div class="p-4 rounded-2xl border-2 border-navy/20 bg-white/70 shadow-xs">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Paket Semester</div>
                            <div class="text-xl font-extrabold text-navy mt-1">Rp 1.500.000</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">per semester (6 bulan) • Akses penuh BK</div>
                        </div>

                        <div class="p-4 rounded-2xl border-2 border-[#00bfc3] bg-white/90 shadow-xs relative overflow-hidden">
                            <span class="absolute top-2 right-2 text-[10px] font-black bg-emerald-500 text-white px-2 py-0.5 rounded-full">Hemat 16%</span>
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Paket Tahunan</div>
                            <div class="text-xl font-extrabold text-navy mt-1">Rp 2.500.000</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">per tahun (12 bulan) • Rekomendasi Sekolah</div>
                        </div>
                    </div>

                    <!-- Demo Simulation Disclaimer -->
                    <div class="rounded-2xl border border-amber-300 bg-amber-50/90 p-4 text-xs text-amber-900 space-y-1.5 shadow-2xs">
                        <div class="flex items-center gap-1.5 font-bold text-amber-950">
                            <span>💳</span>
                            <span>Layanan Berlangganan Berbayar (Tahap Uji Coba Demo)</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Fitur ini merupakan layanan berbayar kemitraan B2B untuk sekolah. Sistem transaksi pembayaran saat ini masih dalam <strong>tahap demo / uji coba inovasi</strong>, sehingga Anda dapat melakukan <strong>simulasi pembayaran</strong> untuk mengaktifkan seluruh fitur analitik tanpa pemotongan biaya riil.
                        </p>
                    </div>

                    <div class="flex flex-col items-center gap-3 pt-2">
                        @if (session('success'))
                            <p role="status" class="w-full rounded-xl bg-white/90 border border-emerald-200 px-4 py-3 text-center text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
                        @endif

                        @if (session('info'))
                            <p role="status" class="w-full rounded-xl bg-white/90 border border-blue-200 px-4 py-3 text-center text-sm font-semibold text-blue-800">{{ session('info') }}</p>
                        @endif

                        @if (auth()->user()->isPremium())
                            <div class="flex w-full flex-col sm:flex-row items-center gap-3">
                                <span class="flex min-h-11 flex-1 w-full items-center justify-center rounded-full bg-[#00bfc3] px-5 text-sm font-semibold text-white shadow-sm">
                                    ✨ Paket B2B Sekolah Aktif
                                </span>
                                <form method="POST" action="{{ route('premium.deactivate') }}" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-gray-500 hover:text-red-500 underline py-2 px-3 transition-colors cursor-pointer">
                                        (Reset ke Free Mode)
                                    </button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('premium.activate') }}" class="w-full">
                                @csrf
                                <button type="submit" class="flex min-h-11 w-full items-center justify-center rounded-full bg-navy px-5 text-sm font-semibold text-white transition hover:bg-[#183a69] focus:outline-none focus:ring-2 focus:ring-navy focus:ring-offset-2 shadow-md hover:shadow-lg active:scale-95 cursor-pointer">
                                    💳 Simulasikan Pembayaran & Aktifkan Paket Sekolah
                                </button>
                            </form>
                        @endif

                        <div class="flex items-center gap-3 text-xs text-[#526d83] mt-1">
                            <a href="{{ route('dashboard') }}" class="hover:underline text-navy font-semibold">← Kembali ke Dashboard</a>
                            <span>•</span>
                            <a href="{{ route('teacher.students') }}" class="hover:underline text-navy font-semibold">Lihat Student Wellbeing →</a>
                        </div>
                    </div>
                </article>
            @else
                {{-- ==================== STUDENT / TEENAGER VIEW ==================== --}}
                <div class="text-center">
                    <h1 class="text-3xl font-extrabold leading-tight text-navy sm:text-5xl">AUREA <span class="text-[#efb323]">Premium</span></h1>
                    <p class="mt-2 text-sm text-navy sm:text-base">More space to grow, one small step every day.</p>
                </div>

                <article class="w-full rounded-[26px] border-2 border-aurea bg-gradient-to-br from-[#bff8f7] via-[#dcfbfa] to-[#f8ffff] p-5 shadow-[0_24px_70px_-38px_rgba(0,15,46,0.45)] sm:rounded-[30px] sm:p-8">
                    <div class="grid items-center gap-5 sm:grid-cols-[minmax(0,1fr)_150px] sm:gap-8">
                        <div>
                            <h2 class="text-xl font-bold text-navy sm:text-2xl">Comprehensive support</h2>
                            <ul class="mt-4 space-y-3 text-sm font-medium text-[#008e95] sm:text-base">
                                <li class="flex gap-2"><span aria-hidden="true">✦</span><span>Unlimited companion chats with AI Aurea</span></li>
                                <li class="flex gap-2"><span aria-hidden="true">✦</span><span>Track as many habits as you need</span></li>
                                <li class="flex gap-2"><span aria-hidden="true">✦</span><span>View weekly and monthly analytics</span></li>
                                <li class="flex gap-2"><span aria-hidden="true">✦</span><span>Smart reminders tailored to your routine</span></li>
                                <li class="flex gap-2"><span aria-hidden="true">✦</span><span><strong>Kostumisasi Skin Pohon:</strong> Bebas pilih tema pohon (Sakura, Cemara, Musim Gugur, Natal, Apel Buah) di Habit Growth Tree</span></li>
                            </ul>
                        </div>
                        <img src="{{ asset('images/maskot_aurea.png') }}" alt="Aurea Mascot" class="mx-auto h-36 w-20 max-w-full object-contain sm:h-44 sm:w-24">
                    </div>

                    <div class="mt-6 flex flex-col items-center gap-3 sm:mt-8">
                        @if (session('success'))
                            <p role="status" class="w-full rounded-xl bg-white/80 px-4 py-3 text-center text-sm font-medium text-[#008e95]">{{ session('success') }}</p>
                        @endif

                        @if (auth()->user()->isPremium())
                            <span class="flex min-h-11 w-full items-center justify-center rounded-full bg-[#00bfc3] px-5 text-sm font-semibold text-white">Premium Active</span>
                            <a href="{{ route('habit-growth-tree') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-navy hover:underline mt-0.5">
                                <span>🎨</span>
                                <span>Kostumisasi Skin Pohon Sekarang →</span>
                            </a>
                        @else
                            <form method="POST" action="{{ route('premium.activate') }}" class="w-full">
                                @csrf
                                <button type="submit" class="flex min-h-11 w-full items-center justify-center rounded-full bg-navy px-5 text-sm font-semibold text-white transition hover:bg-[#183a69] focus:outline-none focus:ring-2 focus:ring-navy focus:ring-offset-2 cursor-pointer">
                                    Activate Premium
                                </button>
                            </form>
                        @endif

                        <p class="text-center text-xs text-[#526d83]">Demo mode: all Premium features are unlocked for free during trial.</p>
                    </div>
                </article>
            @endif
        </section>
    </div>
</x-app-layout>
