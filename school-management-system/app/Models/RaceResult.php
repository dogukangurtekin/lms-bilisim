<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'user_name',
        'progress',
        'wpm',
        'accuracy',
        'elapsed_seconds',
        'completion_seconds',
        'xp_earned',
        'finished_at',
        'is_spectator',
    ];

    protected function casts(): array
    {
        return [
            'finished_at' => 'datetime',
            'is_spectator' => 'boolean',
            'elapsed_seconds' => 'integer',
            'completion_seconds' => 'integer',
            'xp_earned' => 'integer',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
