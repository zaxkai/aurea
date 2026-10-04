<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentalProfile extends Model
{
    protected $fillable = [
        'user_id', 'symptoms', 'anxiety_triggers',
        'bad_experiences', 'focus_areas', 'completed_at',
    ];

    protected $casts = [
        'symptoms' => 'encrypted',
        'anxiety_triggers' => 'encrypted',
        'bad_experiences' => 'encrypted',
        'focus_areas' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
