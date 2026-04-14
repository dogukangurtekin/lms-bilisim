<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_homeworks', function (Blueprint $table) {
            if (! Schema::hasColumn('course_homeworks', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('game_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('game_assignments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_homeworks', function (Blueprint $table) {
            if (Schema::hasColumn('course_homeworks', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('game_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('game_assignments', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};

