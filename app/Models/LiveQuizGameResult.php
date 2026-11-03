<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Canlı quiz soru aralarındaki mini oyunda öğrencinin tamamladığı bir bölüm ve kazandığı XP. */
class LiveQuizGameResult extends Model
{
    protected $fillable = [
        'live_quiz_session_id',
        'student_user_id',
        'break_index',
        'level_key',
        'level_no',
        'xp',
        'completed_at_ms',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveQuizSession::class, 'live_quiz_session_id')->withTrashed();
    }

    public function studentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }
}
