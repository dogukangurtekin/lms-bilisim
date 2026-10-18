<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBankItem extends Model
{
    protected $fillable = [
        'owner_id', 'subject', 'grade_level', 'unit', 'difficulty', 'type', 'body',
        'options', 'answer', 'image_path', 'tags', 'is_shared',
    ];

    protected $casts = ['options' => 'array', 'is_shared' => 'boolean'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
