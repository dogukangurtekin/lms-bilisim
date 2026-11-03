<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_quiz_sessions', function (Blueprint $table) {
            // Soru arası: 'wait' = 5 sn bekle, 'game' = Oyun ve Etkinlikler'den mini oyun (30 sn, 10 bölüm)
            $table->string('between_mode', 10)->default('wait')->after('phase');
            $table->string('between_game', 60)->nullable()->after('between_mode');
            // Her soru arası için herkese aynı olan bölüm bloğu: [{"from":23,"to":32}, ...]
            $table->json('between_plan')->nullable()->after('between_game');
        });

        Schema::create('live_quiz_game_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('live_quiz_session_id');
            $table->unsignedBigInteger('student_user_id');
            $table->unsignedSmallInteger('break_index');          // hangi sorudan sonraki mini oyun (0 tabanlı)
            $table->string('level_key', 40);                       // oyunun bildirdiği bölüm kimliği
            $table->unsignedInteger('level_no')->nullable();
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedBigInteger('completed_at_ms');
            $table->timestamps();

            $table->unique(['live_quiz_session_id', 'student_user_id', 'break_index', 'level_key'], 'uq_lq_game_level_once');
            $table->index(['live_quiz_session_id', 'student_user_id'], 'idx_lq_game_session_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_quiz_game_results');
        Schema::table('live_quiz_sessions', function (Blueprint $table) {
            $table->dropColumn(['between_mode', 'between_game', 'between_plan']);
        });
    }
};
