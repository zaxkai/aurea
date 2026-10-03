<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PremiumController extends Controller
{
    public function index(): View
    {
        return view('premium');
    }

    public function activate(): RedirectResponse
    {
        auth()->user()->activatePremium();

        return redirect()
            ->route('premium')
            ->with('success', 'Premium sudah aktif. Selamat menikmati fitur lebih lengkap!');
    }
}
