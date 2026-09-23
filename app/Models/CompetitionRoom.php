<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionRoom extends Model
{
    protected $fillable = [
        'teacher_user_id',
        'game_slug',
        'game_name',
        'school_class_id',
        'level_from',
        'level_to',
        'duration_seconds',
        'join_code',
        'status',
        'started_at_ms',
        'ends_at_ms',
        'finished_at_ms',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CompetitionParticipant::class);
    }
}
