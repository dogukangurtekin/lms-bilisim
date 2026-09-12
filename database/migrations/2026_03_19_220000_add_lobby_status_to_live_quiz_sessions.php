<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kahoot tarzi "herkes ayni anda baslasin" lobisi icin yeni bir durum ekleniyor.
        // enum yerine VARCHAR'a genisletiliyor ki ileride yeni durumlar eklemek migration
        // gerektirmesin.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE live_quiz_sessions MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'lobby'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE live_quiz_sessions SET status = 'live' WHERE status = 'lobby'");
            DB::statement("ALTER TABLE live_quiz_sessions MODIFY COLUMN status ENUM('live','finished') NOT NULL DEFAULT 'live'");
        }
    }
};
