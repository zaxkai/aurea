<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mentalProfile()
    {
        return $this->hasOne(MentalProfile::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    public function habits()
    {
        return $this->hasMany(Habit::class);
    }

    public function tree()
    {
        return $this->hasOne(Tree::class);
    }

    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }
}