<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logo_aurea.png') }}">

        <title>{{ config('app.name', 'AUREA - AI for User Resilience & Everyday Awareness') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-bg text-navy min-h-dvh w-full selection:bg-aurea selection:text-navy flex flex-col md:flex-row">
        <!-- Hidden navigation for Volt auth/logout functionality and tests -->
        <div class="hidden">
            <livewire:layout.navigation />
        </div>

        <!-- Sidebar -->
        <x-sidebar :active="match (request()->route()?->getName()) {
            'dashboard' => 'home',
            'journal' => 'journal',
            'habit-growth-tree' => 'tree',
            'ai-aurea' => 'ai',
            default => request()->segment(1) ?? 'home',
        }" />

        <!-- Main Content -->
        <div class="flex-1 min-w-0 w-full flex flex-col min-h-dvh max-h-dvh overflow-hidden">
            @unless (request()->routeIs('ai-aurea', 'journal'))
                <!-- Topbar -->
                <header class="bg-white/50 backdrop-blur-sm border-b border-gray-100 px-4 py-3 sm:px-8 sm:py-4 flex items-center justify-between gap-3 shrink-0">
                <div class="hidden min-w-0 max-w-sm flex-1 sm:block">
                    <!-- Search Input -->
                    <div class="relative">
                        <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" placeholder="Search..." class="w-full bg-white border-none rounded-full py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-aurea shadow-sm">
                    </div>
                </div>

                <div class="ml-auto flex items-center gap-3 sm:gap-6">
                    <button class="text-gray-400 hover:text-navy transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                    <button class="text-gray-400 hover:text-navy transition-colors relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-mood-stressed rounded-full"></span>
                    </button>

                    <div class="flex items-center gap-2 border-l border-gray-200 pl-3 sm:gap-3 sm:pl-6">
                        <div class="hidden text-right sm:block">
                            <div class="text-sm font-bold text-navy">{{ auth()->user()->name ?? 'Guest' }}</div>
                            <div class="text-xs text-gray-500 font-medium">Student</div>
                        </div>
                        <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name ?? 'G') . '&color=2EE0E0&background=000F2E' }}" alt="Avatar" class="w-10 h-10 rounded-full shadow-sm">
                    </div>
                </div>
                </header>
            @endunless

            <main class="flex-1 min-w-0 w-full overflow-y-auto p-4 pb-24 sm:p-6 sm:pb-24 md:p-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
