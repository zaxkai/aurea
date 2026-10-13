@props([
    'variant' => 'default',
    'size' => 'default',
    'as' => 'button',
])

@php
$baseClasses = "inline-flex items-center justify-center whitespace-nowrap rounded-base text-sm font-base ring-offset-white transition-all gap-2 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 cursor-pointer select-none";

$variants = [
    'default' => 'text-mtext bg-main border-2 border-border shadow-shadow hover:translate-x-boxShadowX hover:translate-y-boxShadowY hover:shadow-none active:translate-x-boxShadowX active:translate-y-boxShadowY active:shadow-none',
    'noShadow' => 'text-mtext bg-main border-2 border-border',
    'neutral' => 'bg-bw text-text border-2 border-border shadow-shadow hover:translate-x-boxShadowX hover:translate-y-boxShadowY hover:shadow-none active:translate-x-boxShadowX active:translate-y-boxShadowY active:shadow-none',
    'reverse' => 'text-mtext bg-main border-2 border-border hover:translate-x-reverseBoxShadowX hover:translate-y-reverseBoxShadowY hover:shadow-shadow active:translate-x-reverseBoxShadowX active:translate-y-reverseBoxShadowY active:shadow-shadow',
];

$sizes = [
    'default' => 'h-10 px-4 py-2',
    'sm' => 'h-9 px-3',
    'lg' => 'h-11 px-8',
    'icon' => 'h-10 w-10',
];

$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

@if($as === 'a' || $attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
