<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAttendanceRecord extends Model
{
    public const STATUSES = ['present' => 'Geldi', 'absent' => 'Gelmedi', 'excused' => 'İzinli'];

    protected $fillable = ['lesson_attendance_id', 'student_id', 'status', 'first_seen_at', 'source', 'note'];

    protected $casts = ['first_seen_at' => 'datetime'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LessonAttendance::class, 'lesson_attendance_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
