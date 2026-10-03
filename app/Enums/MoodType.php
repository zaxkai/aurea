<?php

namespace App\Enums;

enum MoodType: string
{
    case ENERGETIC = 'energetic';
    case CALM = 'calm';
    case NEUTRAL = 'neutral';
    case STRESSED = 'stressed';
    case EXHAUSTED = 'exhausted';

    public function label(): string
    {
        return match ($this) {
            self::ENERGETIC => 'Energetic',
            self::CALM => 'Calm',
            self::NEUTRAL => 'Neutral',
            self::STRESSED => 'Stressed',
            self::EXHAUSTED => 'Exhausted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ENERGETIC => 'mood-energetic',
            self::CALM => 'mood-calm',
            self::NEUTRAL => 'mood-neutral',
            self::STRESSED => 'mood-stressed',
            self::EXHAUSTED => 'mood-exhausted',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ENERGETIC => '⚡',
            self::CALM => '🌸',
            self::NEUTRAL => '🍃',
            self::STRESSED => '🔥',
            self::EXHAUSTED => '🌧️',
        };
    }
}
