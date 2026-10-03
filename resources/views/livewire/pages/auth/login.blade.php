<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Sign In</h2>
    </div>

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <label for="email" class="block font-medium text-xs text-gray-500 mb-1">Email</label>
            <input wire:model="form.email" id="email" type="email" required autofocus autocomplete="username" class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
            @error('form.email') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ show: false }">
            <div class="flex justify-between items-center mb-1">
                <label for="password" class="block font-medium text-xs text-gray-500">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-xs text-gray-500 hover:text-gray-900" href="{{ route('password.request') }}" wire:navigate>
                        Forgot?
                    </a>
                @endif
            </div>
            <div class="relative">
                <input wire:model="form.password" id="password" :type="show ? 'text' : 'password'" required autocomplete="current-password" class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    <span x-show="!show" class="text-gray-400 hover:text-gray-600">Show</span>
                    <span x-show="show" x-cloak class="text-gray-400 hover:text-gray-600">Hide</span>
                </button>
            </div>
            @error('form.password') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                Sign In
            </button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Don't have an account? <a href="{{ route('register') }}" class="font-bold text-gray-900 hover:underline" wire:navigate>Sign Up</a>
            </p>
        </div>
    </form>
</div>
