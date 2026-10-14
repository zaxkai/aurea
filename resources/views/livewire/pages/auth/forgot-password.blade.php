<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        try {
            $status = Password::sendResetLink(
                $this->only('email')
            );

            if ($status != Password::RESET_LINK_SENT) {
                $this->addError('email', __($status));

                return;
            }

            $this->reset('email');

            session()->flash('status', __($status));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('email', __('Could not send reset email. Please ensure the mail server/SMTP is configured on the server.'));
        }
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Forgot Password') }}</h2>
        <p class="text-xs text-gray-500 mt-2">
            {{ __('Enter your registered email and we will send you a password reset link.') }}
        </p>
    </div>

    <form wire:submit="sendPasswordResetLink">
        @csrf
        <!-- Email Address -->
        <div>
            <label for="email" class="block font-medium text-xs text-gray-500 mb-1">{{ __('Email') }}</label>
            <input wire:model="email" id="email" type="email" required autofocus autocomplete="username" placeholder="name@example.com" class="bg-gray-50 border-transparent focus:border-gray-500 focus:bg-white focus:ring-gray-500 rounded-xl shadow-none block w-full text-sm py-2.5 px-3" />
            @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mt-6">
            <button type="submit" wire:loading.attr="disabled" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition active:scale-[0.99] disabled:opacity-60">
                <span wire:loading.remove>{{ __('Send Reset Link') }}</span>
                <span wire:loading class="flex items-center gap-2">
                    <x-ui.message-loading class="w-5 h-5 text-white" />
                    {{ __('Sending link...') }}
                </span>
            </button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                {{ __('Remember your password?') }} <a href="{{ route('login') }}" class="font-bold text-gray-900 hover:underline" wire:navigate>{{ __('Sign In') }}</a>
            </p>
        </div>
    </form>
</div>
