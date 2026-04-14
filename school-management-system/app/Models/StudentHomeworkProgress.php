<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentHomeworkProgress extends Model
{
    protected $table = 'student_homework_progresses';

    protected $fillable = [
        'course_homework_id',
        'student_id',
        'started_at',
        'completed_at',
        'xp_awarded',
        'level_from',
        'level_to',
        'reached_level',
        'completion_seconds',
        'completion_payload',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'completion_payload' => 'array',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(CourseHomework::class, 'course_homework_id')->withTrashed();
    }
}
