<x-app-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-navy tracking-tight">Habit Growth Tree</h1>
        <p class="text-gray-500 text-sm mt-1">Pohon ketahanan dirimu tumbuh seiring kebiasaan baik yang kamu jalankan setiap hari.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-7 max-w-4xl">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
            <h2 class="text-lg font-bold text-navy mb-4">Pohon Ketahanan Pribadi</h2>

            @php
                $growth = auth()->user()->tree?->growth_percentage ?? 30;
            @endphp

            <div class="w-48 h-56 relative my-4">
                <svg viewBox="0 0 100 120" class="w-full h-full drop-shadow-md">
                    <defs>
                        <clipPath id="pageTreeClip">
                            <polygon points="50,10 75,40 65,40 85,70 70,70 90,95 10,95 30,70 15,70 35,40 25,40" />
                        </clipPath>
                    </defs>

                    <polygon points="50,10 75,40 65,40 85,70 70,70 90,95 10,95 30,70 15,70 35,40 25,40"
                             fill="none"
                             stroke="#0B1224"
                             stroke-width="2.5"
                             stroke-linejoin="round" />

                    <g clip-path="url(#pageTreeClip)">
                        @php
                            $fillY = 95 - (($growth / 100) * 85);
                            $fillH = ($growth / 100) * 85;
                        @endphp
                        <rect x="0" y="{{ $fillY }}" width="100" height="{{ $fillH }}" fill="#0B1224" />
                        <text x="50" y="{{ min(90, $fillY + ($fillH / 2) + 4) }}"
                              text-anchor="middle"
                              fill="#FFFFFF"
                              font-size="12"
                              font-weight="bold">
                            {{ $growth }}%
                        </text>
                    </g>

                    <rect x="44" y="95" width="12" height="15" fill="#0B1224" rx="1" />
                </svg>
            </div>

            <div class="bg-gray-50 rounded-2xl p-4 w-full text-xs text-gray-600 mt-2">
                Pohon ini tumbuh setiap kali kamu menyelesaikan target kebiasaan harian.
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-navy mb-4">Kebiasaan Aktif</h2>
                <div class="space-y-3">
                    @foreach (auth()->user()->habits as $habit)
                        <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-navy">{{ $habit->name }}</span>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-navy text-white">{{ $habit->target_value }} {{ $habit->unit }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('dashboard') }}" class="w-full text-center py-3 bg-navy text-white text-xs font-bold rounded-full mt-6">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
