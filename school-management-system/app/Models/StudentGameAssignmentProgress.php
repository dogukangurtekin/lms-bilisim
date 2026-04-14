<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGameAssignmentProgress extends Model
{
    protected $table = 'student_game_assignment_progresses';

    protected $fillable = [
        'game_assignment_id',
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

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(GameAssignment::class, 'game_assignment_id')->withTrashed();
    }
}
