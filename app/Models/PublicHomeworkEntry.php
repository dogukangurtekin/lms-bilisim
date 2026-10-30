<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHomeworkEntry extends Model
{
    protected $fillable = ['public_token', 'student_name'];
}
