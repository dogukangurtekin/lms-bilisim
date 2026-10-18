<?php

namespace App\Support;

/**
 * Admin'in "Sınav Oluştur" modülünü öğretmenlere açıp kapatabildiği genel ayar.
 * Küçük bir JSON dosyasında tutulur; dosya yoksa öğretmenlere KAPALI sayılır.
 */
class ExamBuilderSetting
{
    private static ?bool $enabled = null;

    private static function path(): string
    {
        return storage_path('app/exam-builder.json');
    }

    public static function enabled(): bool
    {
        if (self::$enabled !== null) {
            return self::$enabled;
        }

        $enabled = false;
        if (is_file(self::path())) {
            $decoded = json_decode((string) @file_get_contents(self::path()), true);
            if (is_array($decoded) && array_key_exists('enabled', $decoded)) {
                $enabled = (bool) $decoded['enabled'];
            }
        }

        return self::$enabled = $enabled;
    }

    public static function set(bool $enabled): void
    {
        file_put_contents(self::path(), json_encode(['enabled' => $enabled]), LOCK_EX);
        self::$enabled = $enabled;
    }

    /** Admin her zaman; öğretmen yalnızca ayar açıksa. */
    public static function allowedFor(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || ($user->hasRole('teacher') && self::enabled());
    }
}
