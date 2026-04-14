<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'game_slug',
        'game_name',
        'title',
        'due_date',
        'level_from',
        'level_to',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'game_assignment_school_class');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(GameAssignmentLevel::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
