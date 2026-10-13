<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HabitLog extends Model
{
    protected $fillable = ['habit_id', 'log_date', 'value_logged'];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'value_logged' => 'integer',
        ];
    }

    public function habit()
    {
        return $this->belongsTo(Habit::class);
    }
}
