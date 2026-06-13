<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = ['activity_question_id','option_key','label','is_correct','order_no'];
    protected $casts = ['is_correct' => 'boolean'];
}
