<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'username',
        'role',
        'xp',
        'total_time_seconds',
        'selected_avatar_id',
        'class_name',
        'section',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (UserProfile $profile): void {
            if (! $profile->exists || ! $profile->isDirty('xp')) {
                return;
            }

            $previousXp = (int) $profile->getOriginal('xp');
            $nextXp = (int) $profile->xp;

            // XP bir ömür boyu kazanım sayacıdır. Avatar harcaması ayrı
            // avatar_xp_spent alanında tutulduğu için profil XP'si normal
            // kayıt/güncelleme akışında hiçbir zaman geriye düşmemelidir.
            if ($nextXp < $previousXp) {
                $profile->xp = $previousXp;
            }
        });
    }
}
