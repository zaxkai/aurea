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

    @if (auth()->user()->isPremium())
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6 border border-aurea">
            <h2 class="font-semibold text-lg mb-2 text-navy">Status Premium</h2>
            <p class="text-sm text-gray-500 mb-4">Kontrol ini hanya untuk mode demo agar juri dapat membandingkan pengalaman Free dan Premium.</p>
            <button wire:click="deactivatePremium" class="rounded-full border border-navy px-5 py-2 text-sm font-semibold text-navy transition hover:bg-gray-50">
                Kembali ke Free
            </button>
        </div>
    @endif

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