<?php

namespace App\Support;

/**
 * 3. - 4. sınıf öğrencilerinin öğretmenine mesaj gönderebilmesini admin açıp kapatır.
 * Küçük bir JSON dosyasında tutulur; dosya yoksa mesaj açıktır (mevcut davranış).
 * Kapalıyken yöneticiye mesaj yazma yolu açık kalır.
 */
class StudentMessageSetting
{
    private static ?bool $g34 = null;

    private static function path(): string
    {
        return storage_path('app/student-message.json');
    }

    private static function g34Open(): bool
    {
        if (self::$g34 === null) {
            self::$g34 = true;
            if (is_file(self::path())) {
                $d = json_decode((string) @file_get_contents(self::path()), true);
                if (is_array($d) && array_key_exists('g34_teacher', $d)) {
                    self::$g34 = (bool) $d['g34_teacher'];
                }
            }
        }

        return self::$g34;
    }

    public static function g34TeacherOpen(): bool
    {
        return self::g34Open();
    }

    public static function saveG34Teacher(bool $open): void
    {
        file_put_contents(self::path(), json_encode(['g34_teacher' => $open]), LOCK_EX);
        self::$g34 = $open;
    }

    /** Bu öğrenci öğretmenine mesaj yazabilir mi? */
    public static function teacherOpenFor($student): bool
    {
        $grade = (int) ($student?->schoolClass?->grade_level ?? 0);

        return ! in_array($grade, [3, 4], true) || self::g34Open();
    }
}
