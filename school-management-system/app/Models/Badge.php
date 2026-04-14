<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = ['name', 'icon', 'description', 'xp_threshold'];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_badge');
    }
}

