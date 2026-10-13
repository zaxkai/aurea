<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                $user->update([
                    'first_name' => $user->first_name ?: ($googleUser->user['given_name'] ?? $googleUser->name),
                    'last_name' => $user->last_name ?: ($googleUser->user['family_name'] ?? ''),
                    'name' => $user->name ?: ($googleUser->name ?? ''),
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar ?: $user->avatar,
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            } else {
                $user = User::create([
                    'email' => $googleUser->email,
                    'first_name' => $googleUser->user['given_name'] ?? $googleUser->name,
                    'last_name' => $googleUser->user['family_name'] ?? '',
                    'name' => $googleUser->name ?? '',
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => bcrypt(Str::random(24)),
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Failed to login with Google.']);
        }
    }
}
