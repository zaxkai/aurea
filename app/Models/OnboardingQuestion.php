<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'question', 'order', 'is_active'];

    public function options()
    {
        return $this->hasMany(OnboardingOption::class, 'question_id');
    }
}
