<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    public const TEMPLATES = [
        'classic' => 'Klasik Yazılı',
        'modern' => 'Modern Renkli',
        'plain' => 'Sade Test',
        'quiz' => 'Kısa Quiz',
        'school' => 'Okul Logolu',
    ];

    protected $fillable = [
        'user_id', 'title', 'subject', 'grade_level', 'class_label', 'term', 'exam_date',
        'duration_min', 'school_name', 'template', 'columns', 'settings', 'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'settings' => 'array',
        'columns' => 'integer',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('sort_order');
    }
}
