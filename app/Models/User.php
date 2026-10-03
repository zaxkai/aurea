<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['first_name', 'last_name', 'name', 'email', 'password', 'google_id', 'avatar', 'current_streak', 'longest_streak', 'last_active_date', 'onboarding_completed_at'])]
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
            'last_check_in_date' => 'date',
            'last_active_date' => 'date',
            'onboarding_completed_at' => 'datetime',
            'is_premium' => 'boolean',
            'premium_started_at' => 'datetime',
            'premium_expires_at' => 'datetime',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name.' '.$this->last_name) ?: ($this->attributes['name'] ?? ''),
            set: fn ($value) => ['name' => $value]
        );
    }

    public function mentalProfile()
    {
        return $this->hasOne(MentalProfile::class);
    }

    public function moodCheckins()
    {
        return $this->hasMany(MoodCheckin::class);
    }

    public function checkIns()
    {
        // Legacy
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

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function tree()
    {
        return $this->hasOne(Tree::class);
    }

    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }

    public function aiUsageLogs(): HasMany
    {
        return $this->hasMany(AiUsageLog::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function onboardingAnswers()
    {
        return $this->hasMany(OnboardingAnswer::class);
    }

    public function isPremium(): bool
    {
        return $this->is_premium
            && ($this->premium_expires_at === null || $this->premium_expires_at->isFuture());
    }

    public function activatePremium(): void
    {
        $this->forceFill([
            'is_premium' => true,
            'premium_started_at' => now(),
            'premium_expires_at' => null,
        ])->save();
    }

    public function deactivatePremium(): void
    {
        $this->forceFill([
            'is_premium' => false,
            'premium_started_at' => null,
            'premium_expires_at' => null,
        ])->save();
    }

    public function onboardingProfile(): string
    {
        $answers = $this->onboardingAnswers()->with(['question', 'option'])->get();
        if ($answers->isEmpty()) {
            return 'No onboarding data.';
        }

        $profile = "User Onboarding Profile:\n";
        foreach ($answers as $answer) {
            $profile .= '- '.$answer->question->question.': '.$answer->option->label."\n";
        }

        return $profile;
    }
}
