<div>
    <h1 class="text-3xl font-bold text-navy">Pengaturan</h1>
    <p class="text-gray-500 mb-8">Atur akun dan preferensimu di sini.</p>

    <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
        <h2 class="font-semibold text-lg mb-4">Akun</h2>
        <form wire:submit="updateProfile" class="space-y-4">
            <div>
                <label class="text-sm text-gray-500">Nama</label>
                <input type="text" wire:model="name" class="w-full rounded-lg bg-gray-100 border-0 px-4 py-2 mt-1">
            </div>
            <div>
                <label class="text-sm text-gray-500">Email</label>
                <input type="email" wire:model="email" class="w-full rounded-lg bg-gray-100 border-0 px-4 py-2 mt-1">
            </div>
            <button type="submit" class="bg-navy text-white px-6 py-2 rounded-full">Simpan</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
        <h2 class="font-semibold text-lg mb-4">Notifikasi</h2>
        <div class="flex items-center justify-between py-2">
            <span>Notifikasi Email</span>
            <input type="checkbox" wire:model.live="notifEmail" class="accent-aurea w-5 h-5">
        </div>
        <div class="flex items-center justify-between py-2">
            <span>Notifikasi Push</span>
            <input type="checkbox" wire:model.live="notifPush" class="accent-aurea w-5 h-5">
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
        <h2 class="font-semibold text-lg mb-4">Privasi & Data</h2>
        <p class="text-sm text-gray-500">
            Data journal dan hasil profile setup kamu dienkripsi dan hanya dipakai untuk personalisasi Aurea.
        </p>
    </div>

    @if (auth()->user()->isStudent())
        <!-- Hubungkan ke Kelas / Guru -->
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-bold text-lg text-navy">Koneksi Kelas Sekolah</h2>
                <span class="text-xs bg-blue-50 text-blue-700 font-bold px-2.5 py-1 rounded-full">Siswa / Murid</span>
            </div>
            <p class="text-xs text-gray-500 mb-4">
                Hubungkan akunmu dengan guru atau wali kelas untuk pemantauan wellbeing. Jika kamu adalah pengguna mandiri, kamu tidak perlu mengisi ini dan datamu tetap sepenuhnya privat.
            </p>

            @if ($classroomMessage)
                <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl">
                    {{ $classroomMessage }}
                </div>
            @endif

            @if ($classroomError)
                <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-xl">
                    {{ $classroomError }}
                </div>
            @endif

            @php
                $enrolledClasses = auth()->user()->enrolledClassrooms()->with('teacher')->get();
            @endphp

            @if ($enrolledClasses->isNotEmpty())
                <div class="space-y-3 mb-5">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas yang kamu ikuti:</div>
                    @foreach ($enrolledClasses as $enrolled)
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <div class="font-bold text-sm text-navy">{{ $enrolled->name }}</div>
                                <div class="text-xs text-gray-500">Guru: {{ $enrolled->teacher?->name ?? 'Guru' }} &bull; Kode: {{ $enrolled->code }}</div>
                            </div>
                            <button type="button"
                                    wire:click="leaveClassroom({{ $enrolled->id }})"
                                    wire:confirm="Yakin ingin keluar dari kelas {{ $enrolled->name }}?"
                                    class="text-xs font-semibold text-rose-600 hover:text-rose-800 px-3 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 transition-colors">
                                Keluar Kelas
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Form Gabung Kelas -->
            <form wire:submit="joinClassroom" class="space-y-3 pt-2 border-t border-gray-100">
                <label class="block text-xs font-bold text-gray-700">Gabung Kelas Baru dengan Kode:</label>
                <div class="flex gap-2">
                    <input type="text"
                           wire:model="classCode"
                           placeholder="Contoh: AUR-89K2"
                           class="flex-1 rounded-xl bg-gray-50 border border-gray-200 px-4 py-2.5 text-sm font-semibold uppercase text-navy focus:outline-none focus:ring-2 focus:ring-navy" />
                    <button type="submit"
                            class="bg-navy text-white text-xs font-bold px-5 py-2.5 rounded-xl hover:bg-[#001744] transition-colors shadow-sm">
                        Gabung Kelas
                    </button>
                </div>
            </form>

            <!-- Disclaimer Jaminan Privasi -->
            <div class="mt-4 p-3 bg-blue-50/70 border border-blue-100 rounded-xl flex items-start gap-2.5">
                <span class="text-base select-none">🛡️</span>
                <p class="text-[11px] text-blue-900 leading-relaxed">
                    <strong>Jaminan Privasi Data:</strong> Ketika terhubung ke kelas, gurumu hanya dapat melihat ringkasan mood check-in dan skor rata-rata wellbeing. <strong>Tulisan jurnal curhat harianmu dan percakapan dengan AI Aurea tetap 100% rahasia</strong> dan tidak dapat diakses oleh siapapun.
                </p>
            </div>
        </div>
    @elseif (auth()->user()->isTeacher())
        <!-- Info Akun Guru -->
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-bold text-lg text-navy">Akun Pendidik / Guru</h2>
                <span class="text-xs bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded-full">Guru</span>
            </div>
            <p class="text-xs text-gray-500 mb-4">
                Kamu terdaftar sebagai Guru di Aurea. Kamu dapat membagikan kode kelas kepada siswa untuk memantau kesejahteraan emosional mereka.
            </p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold bg-navy text-white px-4 py-2.5 rounded-xl hover:bg-[#001744] transition-colors">
                Kelola Kelas di Dashboard &rarr;
            </a>
        </div>
    @endif

    @if (auth()->user()->isPremium())
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6 border border-aurea">
            <h2 class="font-semibold text-lg mb-2 text-navy">Status Premium</h2>
            <p class="text-sm text-gray-500 mb-4">Kontrol ini hanya untuk mode demo agar juri dapat membandingkan pengalaman Free dan Premium.</p>
            <button wire:click="deactivatePremium" class="rounded-full border border-navy px-5 py-2 text-sm font-semibold text-navy transition hover:bg-gray-50">
                Kembali ke Free
            </button>
        </div>
    @endif

    <!-- Log Out Akun -->
    <div class="bg-white rounded-2xl p-6 shadow-sm mb-6 border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-bold text-lg text-navy">Keluar Akun</h2>
            <p class="text-xs text-gray-500 mt-0.5">Keluar dari sesi saat ini di perangkat ini.</p>
        </div>
        <button
            type="button"
            wire:click="logout"
            class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-rose-50 hover:text-rose-600 text-gray-700 font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl border border-gray-200 hover:border-rose-200 transition-colors self-start sm:self-auto cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Log Out</span>
        </button>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-100">
        <h2 class="font-semibold text-lg mb-2 text-red-600">Zona Berbahaya</h2>
        <button
            wire:click="deleteAccount"
            wire:confirm="Yakin ingin menghapus akun? Semua data akan hilang permanen."
            class="text-red-600 text-sm underline">
            Hapus Akun
        </button>
    </div>
</div>