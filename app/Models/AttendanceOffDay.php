<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceOffDay extends Model
{
    protected $fillable = ['date', 'note'];

    protected $casts = ['date' => 'date'];
}
