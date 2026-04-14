<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'user_id', 'student_no', 'school_class_id', 'current_avatar_id', 'avatar_xp_spent', 'parent_name', 'parent_phone', 'birth_date', 'address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function currentAvatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'current_avatar_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function credential(): HasOne
    {
        return $this->hasOne(StudentCredential::class);
    }

    public function avatars(): BelongsToMany
    {
        return $this->belongsToMany(Avatar::class, 'student_avatar');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'student_badge');
    }
}
