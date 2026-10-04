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

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    {{-- Bell Button --}}
    <button @click="open = !open; if(open) { $wire.loadNotifications() }" 
            class="text-gray-400 hover:text-navy transition-colors relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        @if($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-[#F43F72] text-[9px] font-bold text-white ring-2 ring-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 z-50 origin-top-right overflow-hidden flex flex-col"
         style="display: none;">
        
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 bg-gray-50/50">
            <h3 class="font-bold text-navy">Notifications</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-semibold text-aurea hover:text-navy transition">
                    Mark all as read
                </button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="max-h-[60vh] overflow-y-auto overscroll-contain">
            @forelse($notifications as $notification)
                <div class="relative flex gap-4 px-5 py-4 hover:bg-gray-50 transition border-b border-gray-50 last:border-0 {{ $notification->unread() ? 'bg-blue-50/30' : '' }}"
                     wire:click="markAsRead('{{ $notification->id }}')" 
                     style="cursor: pointer;">
                    
                    {{-- Unread dot --}}
                    @if($notification->unread())
                        <div class="absolute left-1.5 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-aurea"></div>
                    @endif

                    {{-- Icon --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $notification->data['type'] === 'success' ? 'bg-emerald-100 text-emerald-600' : ($notification->data['type'] === 'warning' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600') }}">
                        @if(($notification->data['icon'] ?? '') === 'fire')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'tree')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        @elseif(($notification->data['icon'] ?? '') === 'star')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-navy {{ $notification->unread() ? '' : 'text-gray-700' }}">
                            {{ $notification->data['title'] ?? 'Notification' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                            {{ $notification->data['message'] ?? '' }}
                        </p>
                        <p class="text-[10px] font-medium text-gray-400 mt-1.5">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                        @if(!empty($notification->data['link']))
                            <a href="{{ $notification->data['link'] }}" class="inline-block mt-2 text-xs font-semibold text-aurea hover:underline">
                                View details &rarr;
                            </a>
                        @endif
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
            <div class="border-t border-gray-100 bg-gray-50/50 p-2 text-center">
                <span class="text-[11px] font-medium text-gray-400">Only showing latest 10 notifications</span>
            </div>
        @endif
    </div>
</div>
