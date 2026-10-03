<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoodCheckin extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'mood', 'checkin_date', 'note'];

    protected function casts(): array
    {
        return [
            'checkin_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
