<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE courses MODIFY teacher_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE courses SET teacher_id = 1 WHERE teacher_id IS NULL');
        DB::statement('ALTER TABLE courses MODIFY teacher_id BIGINT UNSIGNED NOT NULL');
    }
};
