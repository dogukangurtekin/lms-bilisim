<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentReport extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'total_xp',
        'total_duration_ms',
        'completion_percent',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        // Kazanılmış XP sayacı istemci senkronizasyonuyla geriye düşmemeli.
        static::saving(function (StudentReport $report): void {
            if (! $report->exists || ! $report->isDirty('total_xp')) {
                return;
            }
            if ((int) $report->total_xp < (int) $report->getOriginal('total_xp')) {
                $report->total_xp = (int) $report->getOriginal('total_xp');
            }
        });
    }
}
