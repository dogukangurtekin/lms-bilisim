<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DotConnectLevel extends Model
{
    protected $fillable = [
        'grid_size',
        'name',
        'target_dots',
        'start_point',
        'start_direction',
        'allowed_commands',
        'max_commands',
        'xp',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'target_dots' => 'array',
        'start_point' => 'array',
        'allowed_commands' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Segments are the drawn lines between consecutive target dots,
     * normalized so [a,b] and [b,a] are treated as the same segment.
     */
    public function targetSegments(): array
    {
        $dots = $this->target_dots ?? [];
        $segments = [];
        for ($i = 0; $i < count($dots) - 1; $i++) {
            $segments[] = self::normalizeSegment($dots[$i], $dots[$i + 1]);
        }

        return $segments;
    }

    public static function normalizeSegment(array $a, array $b): string
    {
        $pointA = ($a['x'] ?? 0) . ',' . ($a['y'] ?? 0);
        $pointB = ($b['x'] ?? 0) . ',' . ($b['y'] ?? 0);

        return $pointA < $pointB ? "{$pointA}|{$pointB}" : "{$pointB}|{$pointA}";
    }
}
