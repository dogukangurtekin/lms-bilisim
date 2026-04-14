<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCredential extends Model
{
    protected $fillable = ['student_id', 'username', 'plain_password'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

