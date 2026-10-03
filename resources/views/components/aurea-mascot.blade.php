@props(['mood' => 'neutral', 'size' => 'md'])

@php
    $moodEnum = is_string($mood) ? App\Enums\MoodType::tryFrom($mood) : $mood;
    $moodName = $moodEnum ? $moodEnum->value : 'neutral';

    $sizeClasses = match($size) {
        'sm' => 'w-16 h-16',
        'md' => 'w-32 h-32',
        'lg' => 'w-48 h-48',
        'xl' => 'w-64 h-64',
        default => 'w-32 h-32',
    };
@endphp

<div {{ $attributes->merge(['class' => "relative inline-block $sizeClasses"]) }}>
    <!-- Using a placeholder SVG or pointing to public/images -->
    <img src="{{ asset('images/mascot-' . $moodName . '.svg') }}" alt="Aurea Mascot {{ ucfirst($moodName) }}" class="w-full h-full object-contain" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><circle cx=\'50\' cy=\'50\' r=\'40\' fill=\'%232EE0E0\'/><circle cx=\'35\' cy=\'40\' r=\'5\' fill=\'white\'/><circle cx=\'65\' cy=\'40\' r=\'5\' fill=\'white\'/><path d=\'M 35 65 Q 50 80 65 65\' stroke=\'white\' stroke-width=\'5\' stroke-linecap=\'round\' fill=\'none\'/></svg>'">
</div>
