<?php

namespace App\Models;

use App\Support\Utf8Text;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveQuizQuestion extends Model
{
    protected $fillable = [
        'live_quiz_id',
        'sort_order',
        'type',
        'question_text',
        'options',
        'correct_answer',
        'duration_sec',
        'xp',
        'double_xp',
    ];

    protected $casts = [
        'options' => 'array',
        'double_xp' => 'boolean',
    ];

    public function setOptionsAttribute($value): void
    {
        $this->attributes['options'] = json_encode(Utf8Text::sanitizeArray((array) $value), JSON_UNESCAPED_UNICODE);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LiveQuiz::class, 'live_quiz_id');
    }
}
