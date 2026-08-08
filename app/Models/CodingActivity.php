<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodingActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id','created_by','teacher_id','admin_locked','type','title','instruction','lesson_pages','base_xp','time_limit_seconds','meta','is_bonus','active_on','is_active','is_random_pool'
    ];

    protected $casts = [
        'meta' => 'array',
        'lesson_pages' => 'array',
        'is_bonus' => 'boolean',
        'is_active' => 'boolean',
        'is_random_pool' => 'boolean',
        'admin_locked' => 'boolean',
        'active_on' => 'date',
    ];

    public function questions(): HasMany { return $this->hasMany(ActivityQuestion::class)->orderBy('order_no'); }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
