<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\PremiumController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Volt::route('profile-setup', 'pages.profile-setup')
    ->middleware(['auth'])
    ->name('profile-setup');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('dashboard');

Route::view('journal', 'journal')
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('journal');

Route::get('habit-growth-tree', function () {
    $user = auth()->user();
    $tree = $user->tree()->firstOrCreate([], ['growth_percentage' => 30]);
    $tree->refreshGrowthPercentage();

    return view('habit-growth-tree');
})
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('habit-growth-tree');

Route::view('ai-aurea', 'ai-aurea')
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('ai-aurea');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('settings', 'settings')
    ->middleware(['auth', 'onboarding'])
    ->name('settings');

Route::middleware('auth')->group(function () {
    Route::get('premium', [PremiumController::class, 'index'])->name('premium');
    Route::post('premium/activate', [PremiumController::class, 'activate'])->name('premium.activate');
});

Route::post('check-in', [CheckInController::class, 'store'])
    ->middleware(['auth'])
    ->name('checkin.store');

require __DIR__.'/auth.php';
