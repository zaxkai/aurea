<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        /** @var User $user */
        $user = auth()->user();
        $user->activatePremium();

        $message = $user->isTeacher()
            ? 'Paket Berlangganan B2B Sekolah berhasil diaktifkan (Mode Simulasi Demo)!'
            : 'Premium is now active. Enjoy all the enhanced features!';

        return redirect()
            ->route('premium')
            ->with('success', $message);
    }

    public function deactivate(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->deactivatePremium();

        $message = $user->isTeacher()
            ? 'Status akun guru telah dikembalikan ke Akun Free untuk simulasi presentasi.'
            : 'Premium subscription has been deactivated.';

        return redirect()
            ->route('premium')
            ->with('info', $message);
    }
}
