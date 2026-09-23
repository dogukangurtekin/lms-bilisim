<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionParticipant extends Model
{
    protected $fillable = [
        'competition_room_id',
        'student_user_id',
        'user_name',
        'progress_percent',
        'current_level_index',
        'xp_earned',
        'is_spectator',
        'joined_at_ms',
        'finished_at_ms',
    ];

    protected $casts = [
        'is_spectator' => 'boolean',
        'progress_percent' => 'float',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(CompetitionRoom::class, 'competition_room_id');
    }

    public function studentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }
}
