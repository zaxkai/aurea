<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tree extends Model
{
    protected $fillable = ['user_id', 'growth_percentage'];

public function user()
{
    return $this->belongsTo(User::class);
}   
}
