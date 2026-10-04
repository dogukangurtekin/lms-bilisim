<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'url',
        'route_name',
        'action_label',
        'method',
        'ip',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
