<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LessonSlot extends Model
{
    public const WEEKDAYS = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];

    protected $fillable = ['school_class_id', 'weekday', 'lesson_no', 'start_time', 'end_time', 'subject', 'teacher_user_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    /** Belirli bir tarihteki başlangıç / bitiş anı. */
    public function startOn(Carbon $date): Carbon
    {
        return $date->copy()->setTimeFromTimeString($this->start_time);
    }

    public function endOn(Carbon $date): Carbon
    {
        return $date->copy()->setTimeFromTimeString($this->end_time);
    }

    public function timeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5) . ' - ' . substr((string) $this->end_time, 0, 5);
    }
}
