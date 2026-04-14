<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAssignmentLevel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'game_assignment_id',
        'level',
        'points',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(GameAssignment::class, 'game_assignment_id');
    }
}

