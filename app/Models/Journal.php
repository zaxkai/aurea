<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = ['user_id', 'title', 'content', 'mood', 'summary', 'advice', 'journal_date'];

    protected $casts = [
        'content' => 'encrypted',
        'summary' => 'encrypted',
        'advice' => 'encrypted',
        'journal_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
