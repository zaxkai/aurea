<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JournalThemeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validThemes = implode(',', array_keys(User::AVAILABLE_JOURNAL_THEMES));

        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:'.$validThemes],
        ]);

        $selectedTheme = $validated['theme'];
        $themeConfig = User::AVAILABLE_JOURNAL_THEMES[$selectedTheme];

        /** @var User $user */
        $user = auth()->user();

        if ($themeConfig['is_premium'] && ! $user->isPremium()) {
            return back()->with('error', 'This journal theme is an exclusive Aurea Premium feature. Please activate Premium first!');
        }

        $user->update(['journal_theme' => $selectedTheme]);

        return back()->with('success', "Journal theme changed to {$themeConfig['name']} successfully!");
    }
}
