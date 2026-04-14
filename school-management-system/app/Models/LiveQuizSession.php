<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveQuizSession extends Model
{
    protected $fillable = [
        'live_quiz_id',
        'teacher_user_id',
        'join_code',
        'status',
        'current_index',
        'is_locked',
        'started_at_ms',
        'ends_at_ms',
        'finished_at_ms',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LiveQuiz::class, 'live_quiz_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LiveQuizAnswer::class, 'live_quiz_session_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(LiveQuizParticipant::class, 'live_quiz_session_id');
    }
}
