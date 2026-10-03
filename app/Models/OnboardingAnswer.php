<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'question_id', 'option_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(OnboardingQuestion::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(OnboardingOption::class, 'option_id');
    }
}
