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
        'table' => 'Tablolu (Okul)',
    ];

    /** Çalışma kâğıdında cevapların çıktıya nasıl yazılacağı. */
    public const ANSWER_MODES = [
        'inline' => 'Her sorunun yanında cevaplı',
        'key_end' => 'Sonda cevap anahtarı',
        'none' => 'Cevapsız (boş çalışma kâğıdı)',
    ];

    public const SUBJECTS = ['Bilişim Teknolojileri ve Yazılım'];

    public const SCHOOLS = [
        'Özel Gebze Özelsin Koleji Ortaokulu',
        'Özel Gebze Özelsin Koleji Anadolu Lisesi',
        'Özel Gebze Özelsin Koleji Fen Lisesi',
        'Özel Gebze Özelsin Koleji Fen ve Teknoloji Lisesi',
    ];

    protected $fillable = [
        'kind', 'user_id', 'title', 'subject', 'grade_level', 'class_label', 'term', 'exam_date',
        'duration_min', 'school_name', 'template', 'columns', 'settings', 'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'settings' => 'array',
        'columns' => 'integer',
    ];

    public function isWorksheet(): bool
    {
        return ($this->kind ?? 'exam') === 'worksheet';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('sort_order');
    }
}
