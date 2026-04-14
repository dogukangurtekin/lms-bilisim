<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE course_homeworks MODIFY course_id BIGINT UNSIGNED NULL');
        Schema::table('course_homeworks', function (Blueprint $table) {
            $table->string('attachment_path', 255)->nullable()->after('details');
            $table->string('attachment_original_name', 255)->nullable()->after('attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('course_homeworks', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_original_name']);
        });
        DB::statement('ALTER TABLE course_homeworks MODIFY course_id BIGINT UNSIGNED NOT NULL');
    }
};
