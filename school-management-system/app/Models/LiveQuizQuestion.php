<?php

namespace App\Models;

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
        $this->attributes['options'] = json_encode($this->utf8ize($value), JSON_UNESCAPED_UNICODE);
    }

    private function utf8ize($value)
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$this->utf8ize($k)] = $this->utf8ize($v);
            }
            return $out;
        }

        if (is_string($value)) {
            $converted = @mb_convert_encoding($value, 'UTF-8', 'UTF-8, Windows-1254, ISO-8859-9, ISO-8859-1');
            return is_string($converted) ? $converted : utf8_encode($value);
        }

        return $value;
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LiveQuiz::class, 'live_quiz_id');
    }
}
