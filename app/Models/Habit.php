<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    protected $fillable = ['user_id', 'name', 'unit', 'target_value', 'type', 'is_active', 'source', 'recommendation_key'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'target_value' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(HabitLog::class);
    }
}
