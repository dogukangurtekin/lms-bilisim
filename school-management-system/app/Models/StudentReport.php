<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentReport extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'total_xp',
        'total_duration_ms',
        'completion_percent',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
