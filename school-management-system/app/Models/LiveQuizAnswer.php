<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveQuizAnswer extends Model
{
    protected $fillable = [
        'live_quiz_session_id',
        'student_user_id',
        'question_index',
        'selected_answer',
        'is_correct',
        'xp_earned',
        'answered_at_ms',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveQuizSession::class, 'live_quiz_session_id');
    }

    public function studentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }
}

