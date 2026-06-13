<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['coding_activity_id','question_type','prompt','answer_key','points','order_no'];
    protected $casts = ['answer_key' => 'array'];

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order_no');
    }
}