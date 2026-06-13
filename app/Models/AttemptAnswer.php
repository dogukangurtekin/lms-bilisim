<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['activity_attempt_id','activity_question_id','answer_payload','awarded_points'];
    protected $casts = ['answer_payload' => 'array'];

    public function activityQuestion()
    {
        return $this->belongsTo(ActivityQuestion::class, 'activity_question_id');
    }
}