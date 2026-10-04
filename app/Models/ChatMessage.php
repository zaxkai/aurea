<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['chat_session_id', 'role', 'content'];

    protected $casts = [
        'content' => 'encrypted',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class);
    }
}
