<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $password = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', Rules\Password::defaults()],
        ]);

        $validated['name'] = trim($this->first_name.' '.$this->last_name);
        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('profile-setup', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Sign Up</h2>
    </div>

    <form wire:submit="register">
        <div class="flex gap-4">
            <!-- First Name -->
            <div class="w-1/2">
                <label for="first_name" class="block font-medium text-xs text-gray-500 mb-1">First Name</label>
                <input wire:model="first_name" id="first_name" type="text" required autofocus class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
                @error('first_name') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Last Name -->
            <div class="w-1/2">
                <label for="last_name" class="block font-medium text-xs text-gray-500 mb-1">Last Name (optional)</label>
                <input wire:model="last_name" id="last_name" type="text" class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
                @error('last_name') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block font-medium text-xs text-gray-500 mb-1">Email</label>
            <input wire:model="email" id="email" type="email" required class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
            @error('email') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ show: false }">
            <label for="password" class="block font-medium text-xs text-gray-500 mb-1">Password</label>
            <div class="relative">
                <input wire:model="password" id="password" :type="show ? 'text' : 'password'" required class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    <span x-show="!show" class="text-gray-400 hover:text-gray-600">Show</span>
                    <span x-show="show" x-cloak class="text-gray-400 hover:text-gray-600">Hide</span>
                </button>
            </div>
            @error('password') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                Sign Up
            </button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Already have an account? <a href="{{ route('login') }}" class="font-bold text-gray-900 hover:underline" wire:navigate>Sign In</a>
            </p>
        </div>
    </form>
</div>
