<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookTaskProgress extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'completed',
        'approved',
        'payload',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'approved' => 'boolean',
        'payload' => 'array',
    ];
}
