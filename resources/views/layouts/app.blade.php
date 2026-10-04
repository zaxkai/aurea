<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logo_aurea.png') }}">

        <title>{{ config('app.name', 'Aurea') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-bg text-navy h-dvh w-full overflow-hidden selection:bg-aurea selection:text-navy flex flex-col md:flex-row">
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
        <div class="flex h-dvh min-h-0 min-w-0 w-full flex-1 flex-col overflow-hidden">
            @unless (request()->routeIs('ai-aurea'))
                <!-- Topbar -->
                <header class="relative z-30 bg-white border-b border-gray-100 px-4 py-3 sm:px-8 sm:py-3.5 flex items-center justify-between gap-4 shrink-0 shadow-sm">
                <div class="min-w-0 max-w-md flex-1">
                    <!-- Global Search Livewire Component -->
                    <livewire:global-search />
                </div>

                <div class="ml-auto flex items-center gap-3 sm:gap-6">
                    <button class="text-gray-400 hover:text-navy transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                    <livewire:layout.notification-dropdown />

                    <a href="{{ route('profile') }}" class="flex items-center gap-2 border-l border-gray-200 pl-3 sm:gap-3 sm:pl-6 group">
                        <div class="hidden text-right sm:block">
                            <div class="text-sm font-bold text-navy group-hover:text-aurea transition-colors">{{ auth()->user()->name ?? 'Guest' }}</div>
                            <div class="text-xs text-gray-500 font-medium">Student</div>
                        </div>
                        <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-10 h-10 rounded-full shadow-sm ring-2 ring-transparent group-hover:ring-aurea transition-all">
                    </a>
                </div>
                </header>
            @endunless

            <main class="min-h-0 min-w-0 w-full flex-1 overflow-y-auto p-4 pb-24 sm:p-6 sm:pb-24 md:p-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
