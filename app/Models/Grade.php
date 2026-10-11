<?php

namespace App\Models;

use App\Services\StudentXpService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = ['student_id', 'course_id', 'exam_type', 'score', 'exam_date', 'term'];

    protected static function booted(): void
    {
        // Not puanı XP olarak sayılır; kalıcı XP sayaçlarına da yansıtılır.
        static::saved(function (Grade $grade): void {
            $previous = $grade->wasRecentlyCreated ? 0 : (int) round((float) $grade->getOriginal('score'));
            $delta = (int) round((float) $grade->score) - $previous;
            $userId = Student::query()->whereKey($grade->student_id)->value('user_id');
            app(StudentXpService::class)->award($userId ? (int) $userId : null, $delta);
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
