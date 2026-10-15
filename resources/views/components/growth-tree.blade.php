@props(['stage', 'skin' => null, 'compact' => false])

@php
    $user = auth()->user();
    $treeSkin = $skin ?? $user?->tree?->skin ?? 'default';

    // If a premium skin is selected but user is not premium (and no explicit preview skin was passed), fallback to default
    if ($skin === null && $treeSkin !== 'default' && (! $user || ! $user->isPremium())) {
        $treeSkin = 'default';
    }

    $imageSrc = $treeSkin !== 'default' && file_exists(public_path("images/habit-growth-tree/skins/{$treeSkin}/pohon{$stage}.png"))
        ? asset("images/habit-growth-tree/skins/{$treeSkin}/pohon{$stage}.png")
        : asset("images/habit-growth-tree/pohon{$stage}.png");
@endphp

<div
    class="growth-tree relative {{ $compact ? 'h-40 w-full max-w-36' : 'h-80 w-full max-w-sm' }}"
    role="img"
    aria-label="Aurea Tree stage {{ $stage + 1 }}"
>
    <img
        src="{{ $imageSrc }}"
        alt=""
        class="relative z-10 h-full w-full object-contain transition-all duration-300"
    >

    @if ($stage >= 2)
        <div class="pointer-events-none absolute inset-0 z-20 overflow-hidden" aria-hidden="true">
            @foreach (range(1, 6) as $leaf)
                <span class="tree-leaf"></span>
            @endforeach
        </div>
    @endif
</div>

