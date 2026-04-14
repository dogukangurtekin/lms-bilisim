<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassBoardPost extends Model
{
    protected $fillable = [
        'school_class_id',
        'student_id',
        'message_key',
        'message',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

