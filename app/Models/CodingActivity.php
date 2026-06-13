<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodingActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id','created_by','type','title','instruction','lesson_pages','base_xp','time_limit_seconds','meta','is_bonus','active_on','is_active','is_random_pool'
    ];

    protected $casts = [
        'meta' => 'array',
        'lesson_pages' => 'array',
        'is_bonus' => 'boolean',
        'is_active' => 'boolean',
        'is_random_pool' => 'boolean',
        'active_on' => 'date',
    ];

    public function questions() { return $this->hasMany(ActivityQuestion::class)->orderBy('order_no'); }
}
