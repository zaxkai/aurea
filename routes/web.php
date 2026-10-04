<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\PremiumController;
use App\Models\Tree;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Volt::route('profile-setup', 'pages.profile-setup')
    ->middleware(['auth'])
    ->name('profile-setup');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('dashboard');

Route::get('teacher/students', function () {
    if (! auth()->user()->isTeacher()) {
        abort(403, 'Akses khusus guru.');
    }

    return view('teacher.students');
})
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('teacher.students');

Route::view('journal', 'journal')
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('journal');

Route::get('habit-growth-tree', function () {
    $user = auth()->user();
    $tree = $user->tree()->firstOrCreate([], ['growth_percentage' => 30]);
    $tree->refreshGrowthPercentage();
    $completedGrowthItemsCount = $tree->completedGrowthItemsCount();

    return view('habit-growth-tree', [
        'completedGrowthItemsCount' => $completedGrowthItemsCount,
        'treeStage' => Tree::growthStageForCompletedItems($completedGrowthItemsCount),
    ]);
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

Route::get('/seed-notifications', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);

        $user = auth()->user();
        if ($user) {
            $user->notify(new AppNotification(
                'Welcome to Premium!',
                'You now have unlimited habits and priority AI responses. Keep growing!',
                'star',
                'success',
                '/premium'
            ));

            $user->notify(new AppNotification(
                '🔥 3 Days Streak!',
                'You are on fire! Keep the momentum going.',
                'fire',
                'warning'
            ));

            $user->notify(new AppNotification(
                'Your Tree is Growing',
                'Your Habit Growth Tree just leveled up. Check it out!',
                'tree',
                'info',
                '/habit-growth-tree'
            ));

            return 'Notifications seeded and migrated successfully! <a href="/dashboard">Go back</a>';
        }

        return 'Please log in first.';
    } catch (Exception $e) {
        return 'Error: '.$e->getMessage();
    }
});
