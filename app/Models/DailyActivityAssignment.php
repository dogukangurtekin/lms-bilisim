<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['assignment_date','coding_activity_id','assigned_by','target_role'];
    protected $casts = ['assignment_date' => 'date'];

    public function activity() { return $this->belongsTo(CodingActivity::class, 'coding_activity_id'); }
}
