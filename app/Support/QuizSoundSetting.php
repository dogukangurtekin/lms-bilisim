<?php

namespace App\Support;

/**
 * Canlı Quiz ses ve müzik ayarı (admin açıp kapatır). Dosya yoksa her şey açıktır.
 * enabled = tüm sesler, music = öğretmen ekranındaki müzik, effects = ses efektleri, volume = 0-100.
 */
class QuizSoundSetting
{
    private static function path(): string
    {
        return storage_path('app/quiz-sound.json');
    }

    public static function all(): array
    {
        $d = [];
        if (is_file(self::path())) {
            $decoded = json_decode((string) @file_get_contents(self::path()), true);
            $d = is_array($decoded) ? $decoded : [];
        }

        return [
            'enabled' => (bool) ($d['enabled'] ?? true),
            'music' => (bool) ($d['music'] ?? true),
            'effects' => (bool) ($d['effects'] ?? true),
            'volume' => max(0, min(100, (int) ($d['volume'] ?? 70))),
        ];
    }

    public static function save(array $data): void
    {
        @file_put_contents(self::path(), json_encode([
            'enabled' => (bool) ($data['enabled'] ?? false),
            'music' => (bool) ($data['music'] ?? false),
            'effects' => (bool) ($data['effects'] ?? false),
            'volume' => max(0, min(100, (int) ($data['volume'] ?? 70))),
        ]), LOCK_EX);
    }
}
