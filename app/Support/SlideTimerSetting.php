<?php

namespace App\Support;

/**
 * Öğrenci ders ekranındaki "sayfayı incele" bekleme süresini (slayt saniyesi)
 * admin/öğretmenin açıp kapatabildiği genel ayar. Küçük bir JSON dosyasında
 * tutulur; dosya yoksa süre açık sayılır (mevcut davranış).
 */
class SlideTimerSetting
{
    public const MODE_TIMER = 'timer';
    public const MODE_GAME = 'game';
    public const MODE_OFF = 'off';

    public const MODES = [self::MODE_TIMER, self::MODE_GAME, self::MODE_OFF];

    /** Sınıf grupları: anahtar => etiket. */
    public const GROUPS = [
        'g34' => '3. - 4. Sınıflar',
        'g567' => '5. - 6. - 7. Sınıflar',
        'g910' => '9. - 10. Sınıflar',
        'upo' => 'ÜPO',
    ];

    private static ?array $data = null;

    private static function path(): string
    {
        return storage_path('app/slide-timer.json');
    }

    /**
     * Dosya: {"default":"timer|game|off","groups":{"g34":"game",...}}.
     * Eski biçimler ({"mode":..} / {"enabled":bool}) varsayılan olarak okunur.
     */
    private static function data(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $default = self::MODE_TIMER;
        $groups = [];
        if (is_file(self::path())) {
            $decoded = json_decode((string) @file_get_contents(self::path()), true);
            if (is_array($decoded)) {
                $legacy = $decoded['default'] ?? $decoded['mode'] ?? null;
                if (in_array($legacy, self::MODES, true)) {
                    $default = $legacy;
                } elseif (array_key_exists('enabled', $decoded)) {
                    $default = $decoded['enabled'] ? self::MODE_TIMER : self::MODE_OFF;
                }
                foreach ((array) ($decoded['groups'] ?? []) as $key => $mode) {
                    if (isset(self::GROUPS[$key]) && in_array($mode, self::MODES, true)) {
                        $groups[$key] = $mode;
                    }
                }
            }
        }

        return self::$data = ['default' => $default, 'groups' => $groups];
    }

    /** Öğrencinin sınıfına göre grup anahtarı (eşleşmezse null → varsayılan). */
    public static function groupOf($student): ?string
    {
        $class = $student?->schoolClass;
        if (! $class) {
            return null;
        }
        $name = mb_strtoupper(trim((string) $class->name), 'UTF-8');
        if (str_contains($name, 'ÜPO') || str_contains($name, 'UPO')) {
            return 'upo';
        }

        return match ((int) $class->grade_level) {
            3, 4 => 'g34',
            5, 6, 7 => 'g567',
            9, 10 => 'g910',
            default => null,
        };
    }

    public static function modeForGroup(?string $group): string
    {
        $d = self::data();

        return ($group !== null && isset($d['groups'][$group])) ? $d['groups'][$group] : $d['default'];
    }

    public static function modeFor($student): string
    {
        return self::modeForGroup(self::groupOf($student));
    }

    public static function enabledFor($student): bool
    {
        return self::modeFor($student) === self::MODE_TIMER;
    }

    public static function gameEnabledFor($student): bool
    {
        return self::modeFor($student) === self::MODE_GAME;
    }

    /** Her grup için etkin mod (admin ekranı için). */
    public static function modesByGroup(): array
    {
        return collect(array_keys(self::GROUPS))->mapWithKeys(fn ($g) => [$g => self::modeForGroup($g)])->all();
    }

    /** @param array<string,string> $groups  grup anahtarı => mod */
    public static function saveGroups(array $groups): void
    {
        $clean = [];
        foreach ($groups as $key => $mode) {
            if (isset(self::GROUPS[$key]) && in_array($mode, self::MODES, true)) {
                $clean[$key] = $mode;
            }
        }
        $data = ['default' => self::data()['default'], 'groups' => $clean];
        file_put_contents(self::path(), json_encode($data), LOCK_EX);
        self::$data = $data;
    }
}
