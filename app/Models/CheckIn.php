<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    protected $fillable = [
        'user_id', 'mood', 'who5_score', 'sleep_duration',
        'physical_activity_duration', 'screen_time_duration',
        'wellbeing_index', 'ai_insight', 'note', 'check_in_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'sleep_duration' => 'decimal:2',
            'screen_time_duration' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the mood label in display-friendly format.
     */
    public function getMoodEmojiAttribute(): string
    {
        return match ($this->mood) {
            'energetic' => '😄 Energetic',
            'calm' => '😌 Calm',
            'neutral' => '😐 Neutral',
            'stressed' => '😰 Stressed',
            'exhausted' => '😩 Exhausted',
            default => $this->mood,
        };
    }

    /**
     * Interpret the Well-being Index into a human-readable category.
     */
    public function getWellbeingCategoryAttribute(): string
    {
        $index = $this->wellbeing_index;

        if ($index === null) {
            return 'Belum dihitung';
        }

        return match (true) {
            $index >= 80 => 'Sangat Baik',
            $index >= 60 => 'Baik',
            $index >= 40 => 'Perlu Perhatian',
            default => 'Perlu Tindakan',
        };
    }
}
