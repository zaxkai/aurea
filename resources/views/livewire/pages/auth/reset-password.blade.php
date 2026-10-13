<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Reset Password') }}</h2>
        <p class="text-xs text-gray-500 mt-2">
            {{ __('Please enter your new password below.') }}
        </p>
    </div>

    <form wire:submit="resetPassword">
        @csrf
        <!-- Email Address -->
        <div>
            <label for="email" class="block font-medium text-xs text-gray-500 mb-1">{{ __('Email') }}</label>
            <input wire:model="email" id="email" type="email" required autofocus autocomplete="username" class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
            @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ show: false }">
            <label for="password" class="block font-medium text-xs text-gray-500 mb-1">{{ __('New Password') }}</label>
            <div class="relative">
                <input wire:model="password" id="password" :type="show ? 'text' : 'password'" required autocomplete="new-password" class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    <span x-show="!show" class="text-gray-400 hover:text-gray-600">Show</span>
                    <span x-show="show" x-cloak class="text-gray-400 hover:text-gray-600">Hide</span>
                </button>
            </div>
            @error('password') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mt-4" x-data="{ show: false }">
            <label for="password_confirmation" class="block font-medium text-xs text-gray-500 mb-1">{{ __('Confirm Password') }}</label>
            <div class="relative">
                <input wire:model="password_confirmation" id="password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password" class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    <span x-show="!show" class="text-gray-400 hover:text-gray-600">Show</span>
                    <span x-show="show" x-cloak class="text-gray-400 hover:text-gray-600">Hide</span>
                </button>
            </div>
            @error('password_confirmation') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mt-6">
            <button type="submit" wire:loading.attr="disabled" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition active:scale-[0.99] disabled:opacity-60">
                <span wire:loading.remove>{{ __('Reset Password') }}</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('Saving...') }}
                </span>
            </button>
        </div>
    </form>
</div>
