@props(['stage', 'compact' => false])

<div
    class="growth-tree relative {{ $compact ? 'h-40 w-full max-w-36' : 'h-80 w-full max-w-sm' }}"
    role="img"
    aria-label="Pohon Aurea tahap {{ $stage + 1 }}"
>
    <img
        src="{{ asset('images/habit-growth-tree/pohon'.$stage.'.png') }}"
        alt=""
        class="relative z-10 h-full w-full object-contain"
    >

    @if ($stage >= 2)
        <div class="pointer-events-none absolute inset-0 z-20 overflow-hidden" aria-hidden="true">
            @foreach (range(1, 6) as $leaf)
                <span class="tree-leaf"></span>
            @endforeach
        </div>
    @endif
</div>
