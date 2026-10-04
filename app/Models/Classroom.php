<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'name',
        'code',
        'school_name',
        'description',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_student', 'classroom_id', 'student_id')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    /**
     * Generate an uppercase random unique code for classroom joining.
     */
    public static function generateUniqueCode(int $length = 6): string
    {
        do {
            $code = 'AUR-'.strtoupper(Str::random($length));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
