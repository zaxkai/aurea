<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'midtrans_order_id',
        'status',
        'started_at',
        'expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
