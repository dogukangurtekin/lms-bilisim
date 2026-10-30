<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Ödevin "herkese açık" linki: giriş yapmadan, yalnızca linki bilen kişiler açabilir.
 */
class PublicHomework
{
    private static ?bool $available = null;

    /** Veritabanı kolonları (is_public / public_token) hazır mı? Migration çalışmadan form seçeneği gizli kalır. */
    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasColumn('course_homeworks', 'public_token')
                    && Schema::hasColumn('game_assignments', 'public_token');
            } catch (\Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    public static function newToken(): string
    {
        return Str::random(32);
    }

    public static function url(string $token): string
    {
        return url('/acik-odev/' . $token);
    }

    /** Oluşturma sonrası "Ödev bilgilendirme" penceresini tetikler (layout bunu gösterir). */
    public static function flash(string $title, string $token, ?string $due = null): void
    {
        session()->flash('public_homework_link', [
            'title' => $title,
            'url' => self::url($token),
            'due' => $due,
        ]);
    }

    /** Herkese açık ödevde oynatılabilecek (giriş gerektirmeyen) oyun mu? */
    public static function slugIsPlayablePublicly(?string $slug): bool
    {
        return $slug !== null && isset(PublicGamesSetting::GAMES[$slug]);
    }
}
