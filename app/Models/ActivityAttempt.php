<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['coding_activity_id','user_id','status','score','duration_seconds','penalty','started_at','submitted_at'];
    protected $casts = ['started_at' => 'datetime', 'submitted_at' => 'datetime'];

    public function answers()
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function activity()
    {
        return $this->belongsTo(CodingActivity::class, 'coding_activity_id');
    }
}