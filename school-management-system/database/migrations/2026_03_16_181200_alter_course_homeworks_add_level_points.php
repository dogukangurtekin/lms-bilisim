<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_homeworks', function (Blueprint $table) {
            $table->json('level_points')->nullable()->after('level_to');
        });
    }

    public function down(): void
    {
        Schema::table('course_homeworks', function (Blueprint $table) {
            $table->dropColumn('level_points');
        });
    }
};

