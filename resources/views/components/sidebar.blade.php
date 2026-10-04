@props(['active' => 'home'])

<aside class="fixed inset-x-0 bottom-0 z-40 flex h-[72px] w-full items-center justify-between border-t border-gray-100 bg-white px-1 md:static md:h-dvh md:min-h-0 md:w-[180px] md:flex-col md:items-stretch md:border-r md:border-t-0 md:p-3">
    <div class="flex min-w-0 flex-1 items-center md:block">
        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="mb-10 hidden items-center gap-2.5 px-2 group md:flex">
            <div class="w-9 h-9 rounded-full overflow-hidden bg-white shadow-sm ring-1 ring-gray-100">
                <img src="{{ asset('images/logo_aurea.png') }}" alt="Aurea logo" class="w-full h-full object-cover" />
            </div>
            <span class="font-extrabold text-2xl tracking-tight text-navy">aurea</span>
        </a>

        <!-- Navigation Links -->
        <nav class="flex w-full items-center justify-around gap-1 md:block md:space-y-2">
            @if (auth()->user()?->isTeacher())
                <!-- Teacher Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="flex min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-semibold transition-all duration-150 md:flex-row md:justify-start md:gap-3.5 md:px-4 md:py-3 md:text-sm {{ $active === 'home' || $active === 'dashboard' ? 'bg-navy text-white shadow-md shadow-navy/10' : 'text-gray-500 hover:text-navy hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="4" y="4" width="6" height="6" rx="1.4" />
                        <rect x="14" y="4" width="6" height="6" rx="1.4" />
                        <rect x="4" y="14" width="6" height="6" rx="1.4" />
                        <rect x="14" y="14" width="6" height="6" rx="1.4" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Student Wellbeing -->
                <a href="{{ route('teacher.students') }}"
                   class="flex min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-semibold transition-all duration-150 md:flex-row md:justify-start md:gap-3.5 md:px-4 md:py-3 md:text-sm {{ $active === 'teacher-students' ? 'bg-navy text-white shadow-md shadow-navy/10' : 'text-gray-500 hover:text-navy hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    <span class="text-left leading-tight">Student Wellbeing</span>
                </a>
            @else
                <!-- Home -->
                <a href="{{ route('dashboard') }}"
                   class="flex min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-semibold transition-all duration-150 md:flex-row md:justify-start md:gap-3.5 md:px-4 md:py-3 md:text-sm {{ $active === 'home' ? 'bg-navy text-white shadow-md shadow-navy/10' : 'text-gray-500 hover:text-navy hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="4" y="4" width="6" height="6" rx="1.4" />
                        <rect x="14" y="4" width="6" height="6" rx="1.4" />
                        <rect x="4" y="14" width="6" height="6" rx="1.4" />
                        <rect x="14" y="14" width="6" height="6" rx="1.4" />
                    </svg>
                    <span class="md:hidden">Home</span>
                    <span class="hidden md:inline">Home</span>
                </a>

                <!-- Journal -->
                <a href="{{ route('journal') }}"
                   class="flex min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-semibold transition-all duration-150 md:flex-row md:justify-start md:gap-3.5 md:px-4 md:py-3 md:text-sm {{ $active === 'journal' ? 'bg-navy text-white shadow-md shadow-navy/10' : 'text-gray-500 hover:text-navy hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M7 4.5h10a2 2 0 0 1 2 2v13H7a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z" />
                        <path d="M5 17.5a2 2 0 0 1 2-2h12" />
                        <path d="M10 4.5v5l2-1.4 2 1.4v-5" />
                    </svg>
                    <span class="md:hidden">Journal</span>
                    <span class="hidden md:inline">Journal</span>
                </a>

                <!-- Habit Growth Tree -->
                <a href="{{ route('habit-growth-tree') }}"
                   class="flex min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-semibold transition-all duration-150 md:flex-row md:justify-start md:gap-3.5 md:px-4 md:py-3 md:text-sm {{ $active === 'tree' ? 'bg-navy text-white shadow-md shadow-navy/10' : 'text-gray-500 hover:text-navy hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2.8 6.7 9.1h2.8L5.2 14.7h4.5v3.1H8.1v2h7.8v-2h-1.6v-3.1h4.5l-4.3-5.6h2.8L12 2.8Z" />
                    </svg>
                    <span class="md:hidden">Tree</span>
                    <span class="hidden md:inline">Habit Growth Tree</span>
                </a>

                <!-- AI aurea -->
                <a href="{{ route('ai-aurea') }}"
                   class="flex min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-semibold transition-all duration-150 md:flex-row md:justify-start md:gap-3.5 md:px-4 md:py-3 md:text-sm {{ $active === 'ai' ? 'bg-navy text-white shadow-md shadow-navy/10' : 'text-gray-500 hover:text-navy hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3C9.2 6.8 6.5 9.9 6.5 13.4a5.5 5.5 0 0 0 11 0C17.5 9.9 14.8 6.8 12 3Z" />
                        <path d="m9.5 13.2 1.1-1.1 1.1 1.1 1.8-2" />
                        <path d="M9.7 16.1h4.6" />
                    </svg>
                    <span class="md:hidden">AI</span>
                    <span class="hidden md:inline">AI aurea</span>
                </a>
            @endif
        </nav>
    </div>

    <!-- Bottom Settings & Logout -->
    <div class="flex items-center gap-1 border-l border-gray-100 pl-1 md:block md:space-y-2 md:border-l-0 md:border-t md:pl-0 md:pt-4">
        @if (! auth()->user()?->isTeacher())
            <a href="{{ route('premium') }}"
               class="flex min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-semibold transition-all duration-150 md:flex-row md:justify-start md:gap-3.5 md:px-4 md:py-3 md:text-sm {{ $active === 'premium' ? 'bg-navy text-white shadow-md shadow-navy/10' : 'text-yellow-500 hover:text-yellow-600 hover:bg-yellow-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span class="md:hidden">Pro</span>
                <span class="hidden md:inline">Premium</span>
            </a>
        @endif
        <a href="{{ route('settings') }}"
           class="flex min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-[10px] font-semibold transition-all duration-150 md:flex-row md:justify-start md:gap-3.5 md:px-4 md:py-3 md:text-sm {{ $active === 'settings' ? 'bg-navy text-white shadow-md shadow-navy/10' : 'text-gray-500 hover:text-navy hover:bg-gray-50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="md:hidden">Set</span>
            <span class="hidden md:inline">Settings</span>
        </a>
    </div>
</aside>