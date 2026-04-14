<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Avatar extends Model
{
    protected $fillable = ['name', 'image_path', 'required_xp', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_avatar');
    }
}

