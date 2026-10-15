<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $notifications = [];
    public $unreadCount = 0;
    public $isOpen = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        if ($user) {
            try {
                $this->notifications = $user->notifications()->take(10)->get();
                $this->unreadCount = $user->unreadNotifications()->count();
            } catch (\Exception $e) {
                // Table doesn't exist yet, fallback gracefully
                $this->notifications = [];
                $this->unreadCount = 0;
            }
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification && $notification->unread()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }
    
    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen) {
            $this->loadNotifications();
        }
    }
}; ?>

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    {{-- Bell Button --}}
    <button @click="open = !open; if(open) { $wire.loadNotifications() }" 
            type="button"
            aria-label="Toggle notifications"
            class="text-gray-400 hover:text-navy transition-colors relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-aurea/40">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex min-w-4 h-4 px-1 items-center justify-center rounded-full bg-[#F43F72] text-[9px] font-bold text-white ring-2 ring-white leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Mobile Backdrop --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-[#000F2E]/30 backdrop-blur-xs z-40 sm:hidden"
         style="display: none;"></div>

    {{-- Dropdown Panel --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-x-3 top-[68px] max-w-md mx-auto sm:max-w-none sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96 rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 z-50 origin-top sm:origin-top-right overflow-hidden flex flex-col"
         style="display: none;">
        
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 sm:px-5 sm:py-4 bg-gray-50/70">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-sm sm:text-base text-navy">Notifications</h3>
                @if($unreadCount > 0)
                    <span class="rounded-full bg-[#F43F72] text-white text-[10px] font-black px-2 py-0.5">
                        {{ $unreadCount }} new
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($unreadCount > 0)
                    <button wire:click="markAllAsRead" class="text-xs font-semibold text-aurea hover:text-navy transition cursor-pointer">
                        Mark all as read
                    </button>
                @endif
                <button type="button" @click="open = false" aria-label="Close" class="sm:hidden text-gray-400 hover:text-navy p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="max-h-[calc(100dvh-200px)] sm:max-h-[60vh] overflow-y-auto overscroll-contain divide-y divide-gray-50">
            @forelse($notifications as $notification)
                <div wire:key="notif-item-{{ $notification->id }}"
                     class="relative flex gap-3.5 sm:gap-4 px-4 py-3.5 sm:px-5 sm:py-4 hover:bg-gray-50/80 transition {{ $notification->unread() ? 'bg-cyan-50/30' : '' }}"
                     wire:click="markAsRead('{{ $notification->id }}')" 
                     style="cursor: pointer;">
                    
                    {{-- Unread dot --}}
                    @if($notification->unread())
                        <div class="absolute left-1.5 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-aurea"></div>
                    @endif

                    {{-- Icon --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $notification->data['type'] === 'success' ? 'bg-emerald-100 text-emerald-600' : ($notification->data['type'] === 'warning' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600') }}">
                        @if(($notification->data['icon'] ?? '') === 'fire')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'tree')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'star')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'warning')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'student')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'chart')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-navy {{ $notification->unread() ? '' : 'text-gray-700' }}">
                            {{ $notification->data['title'] ?? 'Notification' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2 leading-relaxed">
                            {{ $notification->data['message'] ?? '' }}
                        </p>
                        <div class="flex items-center gap-3 mt-1.5">
                            <span class="text-[10px] font-medium text-gray-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                            @if(!empty($notification->data['link']))
                                <a href="{{ $notification->data['link'] }}" @click.stop wire:navigate class="text-[11px] font-semibold text-aurea hover:underline inline-flex items-center gap-0.5">
                                    <span>View details</span>
                                    <span>&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center flex flex-col items-center justify-center">
                    <div class="h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                        <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-500">All caught up!</p>
                    <p class="text-xs text-gray-400 mt-1">You have no new notifications.</p>
                </div>
            @endforelse
        </div>
        
        {{-- Footer --}}
        @if(count($notifications) > 0)
            <div class="border-t border-gray-100 bg-gray-50/70 py-2.5 px-4 text-center">
                <span class="text-[11px] font-medium text-gray-400">Only showing latest 10 notifications</span>
            </div>
        @endif
    </div>
</div>
