<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id', 'sort_order', 'type', 'body', 'options', 'answer', 'points',
        'image_path', 'section_title', 'answer_lines', 'bank_item_id',
    ];

    protected $casts = ['options' => 'array', 'points' => 'float'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
