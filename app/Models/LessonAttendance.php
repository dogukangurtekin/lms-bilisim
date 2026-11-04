<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonAttendance extends Model
{
    protected $fillable = ['lesson_slot_id', 'school_class_id', 'date', 'start_at', 'end_at', 'subject', 'total_students', 'present_count', 'absent_count', 'finalized_at'];

    protected $casts = [
        'date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(LessonSlot::class, 'lesson_slot_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(LessonAttendanceRecord::class);
    }

    public function classLabel(): string
    {
        $c = $this->schoolClass;

        return $c ? trim($c->name . '/' . $c->section) : '-';
    }
}
