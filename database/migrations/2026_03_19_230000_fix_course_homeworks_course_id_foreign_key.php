<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 27 Agustos'taki kurtarma isleminde "courses" tablosu gecici olarak
     * "courses_broken_20260827" adina cevrilip sonra gercek "courses" tablosu
     * geri yuklenmisti. Ancak birden fazla tablonun course_id foreign key'i
     * artik var olmayan o eski tablo adina isaret etmeye devam ediyordu; bu
     * yuzden ders atamasi/yoklama/not girisi gibi course_id iceren her insert
     * "1452 Cannot add or update a child row" hatasiyla 500 doenuyordu.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Orijinal migration'lardaki ile ayni ON DELETE davranisi korunuyor:
        // attendances -> course silinince course_id NULL'lanir (SET NULL),
        // course_homeworks / grades -> course silinince satir da silinir (CASCADE).
        $onDelete = [
            'course_homeworks' => 'CASCADE',
            'attendances' => 'SET NULL',
            'grades' => 'CASCADE',
        ];

        foreach ($onDelete as $table => $action) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $fk = DB::selectOne("
                SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = 'course_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ", [$table]);

            if (!$fk || $fk->REFERENCED_TABLE_NAME === 'courses') {
                // Zaten dogru tabloya isaret ediyor (ya da constraint yok), yapacak bir sey yok.
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_course_id_foreign` FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE {$action}");
        }
    }

    public function down(): void
    {
        // Hatali eski duruma kasten geri donulmuyor.
    }
};
