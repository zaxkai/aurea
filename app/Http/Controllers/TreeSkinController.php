<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TreeSkinController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validSkins = implode(',', array_keys(Tree::AVAILABLE_SKINS));

        $validated = $request->validate([
            'skin' => ['required', 'string', 'in:'.$validSkins],
        ]);

        $selectedSkin = $validated['skin'];
        $skinConfig = Tree::AVAILABLE_SKINS[$selectedSkin];

        $user = auth()->user();

        if ($skinConfig['is_premium'] && ! $user->isPremium()) {
            return back()->with('error', 'This tree skin is an exclusive Aurea Premium feature. Please activate Premium first!');
        }

        $tree = $user->tree()->firstOrCreate([], ['growth_percentage' => 30]);
        $tree->update(['skin' => $selectedSkin]);

        return back()->with('success', "Tree skin changed to {$skinConfig['name']} successfully!");
    }
}
