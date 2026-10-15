<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['first_name', 'last_name', 'name', 'email', 'password', 'google_id', 'avatar', 'current_streak', 'longest_streak', 'last_active_date', 'last_check_in_date', 'onboarding_completed_at', 'role', 'journal_theme'])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'journal_theme' => 'default',
    ];

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
            get: fn () => trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: ($this->attributes['name'] ?? ''),
            set: function ($value) {
                $parts = explode(' ', trim($value), 2);

                return [
                    'name' => $value,
                    'first_name' => $parts[0] ?? null,
                    'last_name' => $parts[1] ?? null,
                ];
            }
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar
                ? asset('storage/'.$this->avatar)
                : 'https://ui-avatars.com/api/?name='.urlencode($this->name ?: 'G').'&color=2EE0E0&background=000F2E',
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

    public function taughtClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function enrolledClassrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role !== 'teacher';
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

    public const AVAILABLE_JOURNAL_THEMES = [
        'default' => [
            'name' => 'Classic Aurea',
            'badge' => 'Default',
            'is_premium' => false,
            'description' => 'Clean minimalist card design with Aurea\'s iconic cyan leaf mascot.',
            'preview' => 'images/journal/themes/default/preview.png',
            'mascot_badge' => null,
            'bg_image' => null,
            'card_class' => 'bg-white border border-gray-100/60 shadow-xs hover:shadow-sm hover:border-gray-200',
            'title_class' => 'text-navy font-semibold',
            'date_class' => 'text-slate-400 font-medium',
            'content_class' => 'text-slate-500',
            'icon' => '🌿',
            'bar_class' => null,
            'bar_style' => null,
            'date_badge' => null,
            'date_badge_class' => null,
            'fx_type' => null,
        ],
        'sakura' => [
            'name' => 'Cherry Blossom',
            'badge' => 'Premium',
            'is_premium' => true,
            'description' => 'Warm pastel pink aesthetics with blooming sakura branches and a cheerful sakura mascot.',
            'preview' => 'images/journal/themes/sakura/preview.jpg',
            'mascot_badge' => 'images/journal/themes/sakura/mascot-badge.jpg',
            'bg_image' => null,
            'card_class' => 'bg-gradient-to-r from-[#fff5f7] via-white to-[#ffe4eb]/60 border border-pink-200/80 shadow-xs hover:shadow-sm hover:border-pink-300',
            'title_class' => 'text-slate-900 font-bold hover:text-pink-900',
            'date_class' => 'text-pink-600 font-semibold',
            'content_class' => 'text-slate-600',
            'icon' => '🌸',
            'bar_class' => 'bg-gradient-to-b from-pink-400 to-rose-500',
            'bar_style' => null,
            'date_badge' => '🌸 Sakura',
            'date_badge_class' => 'bg-pink-100/90 text-pink-700',
            'fx_type' => 'sakura',
        ],
        'natal' => [
            'name' => 'Christmas Holiday',
            'badge' => 'Premium',
            'is_premium' => true,
            'description' => 'Festive winter holiday mood with glowing fairy lights, candy cane stripes, and Santa pine mascot.',
            'preview' => 'images/journal/themes/natal/preview.jpg',
            'mascot_badge' => 'images/journal/themes/natal/mascot-badge.jpg',
            'bg_image' => null,
            'card_class' => 'bg-gradient-to-r from-[#fefce8]/70 via-white to-[#fef3c7]/50 border border-amber-200/80 shadow-xs hover:shadow-sm hover:border-amber-300',
            'title_class' => 'text-slate-900 font-bold hover:text-emerald-950',
            'date_class' => 'text-emerald-700 font-semibold',
            'content_class' => 'text-slate-600',
            'icon' => '🎄',
            'bar_class' => null,
            'bar_style' => 'background: repeating-linear-gradient(45deg, #dc2626, #dc2626 3px, #ffffff 3px, #ffffff 6px);',
            'date_badge' => '❄️ Winter',
            'date_badge_class' => 'bg-emerald-100/90 text-emerald-800',
            'fx_type' => 'snow',
        ],
        'hujan' => [
            'name' => 'Rainy Season',
            'badge' => 'Premium',
            'is_premium' => true,
            'description' => 'Calm soothing rain ambiance with origami paper boats and a cute cloud mascot in yellow raincoat.',
            'preview' => 'images/journal/themes/hujan/preview.jpg',
            'mascot_badge' => 'images/journal/themes/hujan/mascot-badge.jpg',
            'bg_image' => null,
            'card_class' => 'bg-gradient-to-r from-[#f0f9ff] via-white to-[#e0f2fe]/60 border border-sky-200/80 shadow-xs hover:shadow-sm hover:border-sky-300',
            'title_class' => 'text-slate-900 font-bold hover:text-sky-900',
            'date_class' => 'text-sky-700 font-semibold',
            'content_class' => 'text-slate-600',
            'icon' => '🌧️',
            'bar_class' => 'bg-gradient-to-b from-sky-400 to-blue-500',
            'bar_style' => null,
            'date_badge' => '🌧️ Rainy',
            'date_badge_class' => 'bg-sky-100/90 text-sky-800',
            'fx_type' => 'rain',
        ],
    ];

    public function getJournalThemeDetails(): array
    {
        $theme = $this->journal_theme ?? 'default';

        return self::AVAILABLE_JOURNAL_THEMES[$theme] ?? self::AVAILABLE_JOURNAL_THEMES['default'];
    }
}
