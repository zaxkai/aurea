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
            return back()->with('error', 'Skin pohon ini merupakan fitur eksklusif Aurea Premium. Silakan aktifkan Premium terlebih dahulu!');
        }

        $tree = $user->tree()->firstOrCreate([], ['growth_percentage' => 30]);
        $tree->update(['skin' => $selectedSkin]);

        return back()->with('success', "Skin pohon berhasil diubah menjadi {$skinConfig['name']}!");
    }
}
