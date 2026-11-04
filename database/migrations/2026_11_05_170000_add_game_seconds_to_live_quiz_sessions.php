<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('live_quiz_sessions', 'between_seconds')) {
            Schema::table('live_quiz_sessions', function (Blueprint $table) {
                $table->unsignedSmallInteger('between_seconds')->nullable()->after('between_to');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('live_quiz_sessions', 'between_seconds')) {
            Schema::table('live_quiz_sessions', function (Blueprint $table) {
                $table->dropColumn('between_seconds');
            });
        }
    }
};
