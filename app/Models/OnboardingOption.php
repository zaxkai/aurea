<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingOption extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'label', 'value', 'order'];

    public function question()
    {
        return $this->belongsTo(OnboardingQuestion::class, 'question_id');
    }
}
