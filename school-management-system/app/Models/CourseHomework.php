<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseHomework extends Model
{
    use SoftDeletes;

    protected $table = 'course_homeworks';

    protected $fillable = [
        'course_id',
        'school_class_id',
        'assignment_type',
        'target_slug',
        'title',
        'details',
        'attachment_path',
        'attachment_original_name',
        'due_date',
        'level_from',
        'level_to',
        'level_points',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'level_points' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
