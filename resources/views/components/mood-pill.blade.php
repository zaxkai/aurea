@props(['mood', 'selected' => false])

@php
    $moodEnum = is_string($mood) ? App\Enums\MoodType::tryFrom($mood) : $mood;
    if (!$moodEnum) return;

    $color = $moodEnum->color();
    $baseClasses = "inline-flex items-center gap-2 px-4 py-2 rounded-full font-semibold text-sm transition-all duration-200 cursor-pointer border-2";
    $stateClasses = $selected
        ? "bg-{$color} border-{$color} text-navy shadow-md"
        : "bg-transparent border-gray-200 text-gray-500 hover:border-{$color} hover:bg-{$color}/10";
@endphp

<button type="button" {{ $attributes->merge(['class' => "$baseClasses $stateClasses"]) }}>
    <span class="text-lg">{{ $moodEnum->icon() }}</span>
    <span>{{ $moodEnum->label() }}</span>
</button>
