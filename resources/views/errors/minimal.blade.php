<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_aurea.png') }}">

    <title>@yield('title', 'Aurea')</title>

    <!-- Google Fonts Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Self-contained styling & smooth animations -->
    <style>
        :root {
            --font-family: 'Outfit', system-ui, -apple-system, sans-serif;
            --color-navy: #000F2E;
            --color-aurea: #2EE0E0;
            --color-aurea-dark: #12b5b5;
        }
        body {
            font-family: var(--font-family);
        }
        @keyframes floatMascot {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-12px) rotate(1.5deg);
            }
        }
        @keyframes softPulse {
            0%, 100% {
                opacity: 0.35;
                transform: scale(1);
            }
            50% {
                opacity: 0.65;
                transform: scale(1.08);
            }
        }
        @keyframes speechWiggle {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-4px);
            }
        }
        .animate-float-mascot {
            animation: floatMascot 4.5s ease-in-out infinite;
        }
        .animate-soft-pulse {
            animation: softPulse 5s ease-in-out infinite;
        }
        .animate-speech-wiggle {
            animation: speechWiggle 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="h-full bg-[#f6f9fc] text-[#000F2E] antialiased overflow-x-hidden selection:bg-[#2EE0E0] selection:text-[#000F2E]">
    <div class="relative min-h-screen flex flex-col justify-between overflow-hidden">
        <!-- Ambient Glowing Background Accents -->
        <div class="pointer-events-none absolute -top-32 -left-32 w-96 h-96 rounded-full bg-[#2EE0E0]/20 blur-3xl animate-soft-pulse"></div>
        <div class="pointer-events-none absolute top-1/3 -right-32 w-[28rem] h-[28rem] rounded-full bg-[#D6E64C]/20 blur-3xl animate-soft-pulse" style="animation-delay: -2s;"></div>
        <div class="pointer-events-none absolute -bottom-32 left-1/3 w-[32rem] h-[32rem] rounded-full bg-[#2EE0E0]/15 blur-3xl animate-soft-pulse" style="animation-delay: -3.5s;"></div>

        <!-- Header / Brand Bar -->
        <header class="relative z-10 w-full max-w-6xl mx-auto px-6 py-6 flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group transition-transform hover:scale-105">
                <div class="w-10 h-10 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center p-1.5 transition-shadow group-hover:shadow-md">
                    <img src="{{ asset('images/logo_aurea.png') }}" alt="Aurea Logo" class="w-full h-full object-contain">
                </div>
                <span class="text-2xl font-black tracking-tight text-[#000F2E]">Aurea</span>
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-gray-600 hover:text-[#000F2E] bg-white/80 hover:bg-white backdrop-blur-sm px-4 py-2 rounded-full border border-gray-200/60 shadow-sm transition">
                    <svg class="w-4 h-4 text-[#2EE0E0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            @else
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-gray-600 hover:text-[#000F2E] bg-white/80 hover:bg-white backdrop-blur-sm px-4 py-2 rounded-full border border-gray-200/60 shadow-sm transition">
                    <span>Beranda</span>
                </a>
            @endauth
        </header>

        <!-- Main Content -->
        <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-8 sm:py-12">
            <div class="w-full max-w-2xl mx-auto text-center">
                
                <!-- Mascot & Speech Bubble Section -->
                <div class="relative inline-flex flex-col items-center justify-center mb-6">
                    
                    <!-- Speech Bubble -->
                    <div class="animate-speech-wiggle mb-2 relative z-20">
                        <div class="relative bg-white/95 backdrop-blur-sm text-[#000F2E] font-bold text-xs sm:text-sm py-2 px-4 rounded-2xl shadow-lg shadow-navy/5 border border-cyan-100 flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-[#2EE0E0] animate-ping"></span>
                            <span>@yield('bubble', 'Waduh, sepertinya tersesat ya?')</span>
                            <!-- Speech Bubble Triangle -->
                            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[8px] border-t-white"></div>
                        </div>
                    </div>

                    <!-- Mascot Halo & Image -->
                    <div class="relative w-44 h-48 sm:w-56 sm:h-60 flex items-center justify-center">
                        <!-- Glowing Halo Circle -->
                        <div class="absolute inset-4 rounded-full bg-gradient-to-tr from-[#2EE0E0]/30 to-[#D6E64C]/30 blur-2xl pointer-events-none"></div>
                        
                        <!-- Soft decorative ground shadow -->
                        <div class="absolute bottom-2 w-32 h-4 bg-[#000F2E]/10 rounded-full blur-md"></div>

                        <!-- Mascot Image -->
                        <img src="{{ asset('images/maskot_aurea.png') }}"
                             alt="Maskot Aurea"
                             class="relative z-10 w-full h-full object-contain drop-shadow-xl animate-float-mascot select-none pointer-events-none">
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mb-4">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-widest bg-white border border-[#2EE0E0]/40 text-[#000F2E] shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-[#2EE0E0]"></span>
                        Kode Status: @yield('code', '404')
                    </span>
                </div>

                <!-- Title Heading -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-[#000F2E] tracking-tight mb-3">
                    @hasSection('heading')
                        @yield('heading')
                    @else
                        @yield('message', 'Ups! Terjadi Kesalahan')
                    @endif
                </h1>

                <!-- Friendly Description -->
                <p class="max-w-lg mx-auto text-sm sm:text-base text-gray-600 font-medium leading-relaxed mb-8">
                    @yield('description', 'Halaman yang kamu tuju tidak dapat ditemukan atau sudah dipindahkan. Jangan khawatir, maskot Aurea siap menemanimu kembali!')
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4">
                    <!-- Kembali ke Beranda / Dashboard -->
                    @php
                        $destinationUrl = auth()->check()
                            ? (auth()->user()->onboarding_completed_at ? route('dashboard') : route('profile-setup'))
                            : url('/');
                        $destinationText = auth()->check() ? 'Kembali ke Dashboard' : 'Kembali ke Beranda';
                    @endphp
                    <a href="{{ $destinationUrl }}"
                       class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-full bg-[#000F2E] text-white font-bold text-sm shadow-lg shadow-navy/15 hover:bg-[#08204d] hover:shadow-xl hover:scale-[1.02] active:scale-95 transition-all">
                        <svg class="w-4 h-4 text-[#2EE0E0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>{{ $destinationText }}</span>
                    </a>

                    <!-- Tombol Kembali Sebelumnya -->
                    <button type="button"
                            onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ url('/') }}'; }"
                            class="inline-flex items-center gap-2 px-5 py-3.5 rounded-full bg-white text-[#000F2E] font-bold text-sm border border-gray-200 shadow-sm hover:bg-gray-50 hover:border-gray-300 hover:scale-[1.02] active:scale-95 transition-all">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Halaman Sebelumnya</span>
                    </button>

                    <!-- Tanya AI Aurea jika login -->
                    @auth
                        <a href="{{ route('ai-aurea') }}"
                           class="inline-flex items-center gap-2 px-5 py-3.5 rounded-full bg-[#2EE0E0]/20 text-[#000F2E] font-bold text-sm border border-[#2EE0E0]/50 hover:bg-[#2EE0E0]/30 hover:scale-[1.02] active:scale-95 transition-all">
                            <span class="text-base">✨</span>
                            <span>Tanya AI Aurea</span>
                        </a>
                    @endauth
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 w-full max-w-6xl mx-auto px-6 py-6 text-center">
            <p class="text-xs text-gray-500 font-medium">
                &copy; {{ date('Y') }} Aurea &bull; Sahabat Kesejahteraan & Pengembangan Diri
            </p>
        </footer>
    </div>
</body>
</html>
