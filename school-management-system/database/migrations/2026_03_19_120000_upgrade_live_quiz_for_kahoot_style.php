<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_quiz_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_quiz_session_id')->constrained('live_quiz_sessions')->cascadeOnDelete();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('joined_at_ms')->nullable();
            $table->timestamps();
            $table->unique(['live_quiz_session_id', 'student_user_id'], 'uq_live_quiz_participant_once');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE live_quiz_questions MODIFY COLUMN type VARCHAR(40) NOT NULL DEFAULT 'multiple'");
            DB::statement("ALTER TABLE live_quiz_questions MODIFY COLUMN correct_answer TEXT NOT NULL");
            DB::statement("ALTER TABLE live_quiz_answers MODIFY COLUMN selected_answer TEXT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE live_quiz_questions MODIFY COLUMN type ENUM('multiple','truefalse') NOT NULL DEFAULT 'multiple'");
            DB::statement("ALTER TABLE live_quiz_questions MODIFY COLUMN correct_answer VARCHAR(40) NOT NULL");
            DB::statement("ALTER TABLE live_quiz_answers MODIFY COLUMN selected_answer VARCHAR(200) NULL");
        }

        Schema::dropIfExists('live_quiz_participants');
    }
};

