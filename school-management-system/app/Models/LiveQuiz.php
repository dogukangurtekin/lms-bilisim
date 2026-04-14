<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveQuiz extends Model
{
    protected $fillable = [
        'teacher_user_id',
        'title',
        'school_class_id',
        'join_mode',
        'status',
    ];

    protected $casts = [
        'join_mode' => 'string',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LiveQuizQuestion::class)->orderBy('sort_order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(LiveQuizSession::class)->latest();
    }
}
