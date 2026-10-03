<x-app-layout>
    <div class="relative isolate flex min-h-full items-center justify-center overflow-hidden bg-[linear-gradient(180deg,#f2f3f8_0%,#f2f3f8_42%,#b8f3f1_76%,#56dfdc_100%)] px-4 py-10 sm:px-8">
        <section class="relative flex w-full max-w-3xl flex-col items-center gap-6 sm:gap-7">
            <img src="{{ asset('images/logo_aurea.png') }}" alt="AUREA" class="h-10 w-32 object-contain">
            <div class="text-center">
                <h1 class="text-3xl font-extrabold leading-tight text-navy sm:text-5xl">AUREA <span class="text-[#efb323]">Premium</span></h1>
                <p class="mt-2 text-sm text-navy sm:text-base">Ruang lebih luas untuk tumbuh, satu langkah kecil setiap hari.</p>
            </div>

            <article class="w-full rounded-[26px] border-2 border-aurea bg-gradient-to-br from-[#bff8f7] via-[#dcfbfa] to-[#f8ffff] p-5 shadow-[0_24px_70px_-38px_rgba(0,15,46,0.45)] sm:rounded-[30px] sm:p-8">
                <div class="grid items-center gap-5 sm:grid-cols-[minmax(0,1fr)_150px] sm:gap-8">
                    <div>
                        <h2 class="text-xl font-bold text-navy sm:text-2xl">Dukungan yang lebih lengkap</h2>
                        <ul class="mt-4 space-y-3 text-sm font-medium text-[#008e95] sm:text-base">
                            <li class="flex gap-2"><span aria-hidden="true">✦</span><span>Temani harimu dengan chat AI AUREA tanpa batas</span></li>
                            <li class="flex gap-2"><span aria-hidden="true">✦</span><span>Catat habit sebanyak yang kamu butuhkan</span></li>
                            <li class="flex gap-2"><span aria-hidden="true">✦</span><span>Lihat analitik mingguan dan bulanan</span></li>
                            <li class="flex gap-2"><span aria-hidden="true">✦</span><span>Pengingat yang menyesuaikan kebiasaanmu</span></li>
                        </ul>
                    </div>
                    <img src="{{ asset('images/maskot_aurea.png') }}" alt="Maskot AUREA" class="mx-auto h-36 w-20 max-w-full object-contain sm:h-44 sm:w-24">
                </div>

                <div class="mt-6 flex flex-col items-center gap-3 sm:mt-8">
                    @if (session('success'))
                        <p role="status" class="w-full rounded-xl bg-white/80 px-4 py-3 text-center text-sm font-medium text-[#008e95]">{{ session('success') }}</p>
                    @endif

                    @if (auth()->user()->isPremium())
                        <span class="flex min-h-11 w-full items-center justify-center rounded-full bg-[#00bfc3] px-5 text-sm font-semibold text-white">Premium Aktif</span>
                    @else
                        <form method="POST" action="{{ route('premium.activate') }}" class="w-full">
                            @csrf
                            <button type="submit" class="flex min-h-11 w-full items-center justify-center rounded-full bg-navy px-5 text-sm font-semibold text-white transition hover:bg-[#183a69] focus:outline-none focus:ring-2 focus:ring-navy focus:ring-offset-2">
                                Aktifkan Premium
                            </button>
                        </form>
                    @endif

                    <p class="text-center text-xs text-[#526d83]">Mode demo: semua fitur Premium dibuka gratis untuk percobaan.</p>
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
