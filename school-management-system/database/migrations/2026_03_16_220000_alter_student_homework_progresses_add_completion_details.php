<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_homework_progresses', function (Blueprint $table) {
            $table->unsignedInteger('level_from')->nullable()->after('xp_awarded');
            $table->unsignedInteger('level_to')->nullable()->after('level_from');
            $table->unsignedInteger('reached_level')->nullable()->after('level_to');
            $table->unsignedInteger('completion_seconds')->default(0)->after('reached_level');
            $table->json('completion_payload')->nullable()->after('completion_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('student_homework_progresses', function (Blueprint $table) {
            $table->dropColumn([
                'level_from',
                'level_to',
                'reached_level',
                'completion_seconds',
                'completion_payload',
            ]);
        });
    }
};

