<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'username',
        'role',
        'xp',
        'total_time_seconds',
        'selected_avatar_id',
        'class_name',
        'section',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
