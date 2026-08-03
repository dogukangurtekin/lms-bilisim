<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('game_assignments', 'grade_level_group')) {
                $table->string('grade_level_group', 20)->nullable()->after('level_to');
            }
        });
    }

    public function down(): void
    {
        Schema::table('game_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('game_assignments', 'grade_level_group')) {
                $table->dropColumn('grade_level_group');
            }
        });
    }
};
