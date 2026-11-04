<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_quiz_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('live_quiz_sessions', 'between_from')) {
                $table->unsignedSmallInteger('between_from')->nullable()->after('between_game');
            }
            if (! Schema::hasColumn('live_quiz_sessions', 'between_to')) {
                $table->unsignedSmallInteger('between_to')->nullable()->after('between_from');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_quiz_sessions', function (Blueprint $table) {
            $table->dropColumn(['between_from', 'between_to']);
        });
    }
};
