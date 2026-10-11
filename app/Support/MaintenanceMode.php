<?php

namespace App\Support;

/**
 * Admin'in profil sayfasından açıp kapattığı "bakım çalışması" durumu.
 * Veritabanı yerine küçük bir JSON dosyasında tutulur; böylece her istekte
 * sorgu atılmaz ve veritabanı erişilemezken bile bakım sayfası gösterilebilir.
 */
class MaintenanceMode
{
    private static ?array $state = null;

    private static function path(): string
    {
        return storage_path('app/maintenance-mode.json');
    }

    public static function state(): array
    {
        if (self::$state !== null) {
            return self::$state;
        }

        $state = ['enabled' => false, 'message' => '', 'since' => null];
        $path = self::path();
        if (is_file($path)) {
            $decoded = json_decode((string) @file_get_contents($path), true);
            if (is_array($decoded)) {
                $state = array_merge($state, $decoded);
            }
        }

        return self::$state = $state;
    }

    public static function active(): bool
    {
        return (bool) self::state()['enabled'];
    }

    public static function enable(string $message = ''): void
    {
        self::write([
            'enabled' => true,
            'message' => mb_substr(trim($message), 0, 200),
            'since' => now()->toIso8601String(),
        ]);
    }

    public static function disable(): void
    {
        self::write(['enabled' => false, 'message' => '', 'since' => null]);
    }

    private static function write(array $state): void
    {
        file_put_contents(self::path(), json_encode($state, JSON_UNESCAPED_UNICODE), LOCK_EX);
        self::$state = $state;
    }
}
