<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['assignment_date', 'coding_activity_id', 'assigned_by', 'target_role', 'target_class_ids'];

    protected $casts = [
        'assignment_date' => 'date',
        'target_class_ids' => 'array',
    ];

    public function activity() { return $this->belongsTo(CodingActivity::class, 'coding_activity_id'); }
}
