<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_quiz_sessions', function (Blueprint $table) {
            $table->string('phase', 20)->default('lobby')->after('status')->index();
            $table->unsignedBigInteger('xp_awarded_at_ms')->nullable()->after('finished_at_ms');
        });

        DB::table('live_quiz_sessions')->where('status', 'finished')->update(['phase' => 'finished']);
        DB::table('live_quiz_sessions')->where('status', 'live')->where('is_locked', true)->update(['phase' => 'results']);
        DB::table('live_quiz_sessions')->where('status', 'live')->where('is_locked', false)->update(['phase' => 'question']);
    }

    public function down(): void
    {
        Schema::table('live_quiz_sessions', function (Blueprint $table) {
            $table->dropIndex(['phase']);
            $table->dropColumn(['phase', 'xp_awarded_at_ms']);
        });
    }
};
