<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCourseResume extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'slide_index',
        'state',
    ];

    protected $casts = [
        'slide_index' => 'integer',
        'state' => 'array',
    ];
}
