<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->enum('status', ['draft', 'active', 'archived'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('live_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_quiz_id')->constrained('live_quizzes')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('type', ['multiple', 'truefalse'])->default('multiple');
            $table->text('question_text');
            $table->json('options')->nullable();
            $table->string('correct_answer', 40);
            $table->unsignedInteger('duration_sec')->default(30);
            $table->unsignedInteger('xp')->default(10);
            $table->boolean('double_xp')->default(false);
            $table->timestamps();
        });

        Schema::create('live_quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_quiz_id')->constrained('live_quizzes')->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('join_code', 12)->unique();
            $table->enum('status', ['live', 'finished'])->default('live')->index();
            $table->unsignedInteger('current_index')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('started_at_ms')->nullable();
            $table->unsignedBigInteger('ends_at_ms')->nullable();
            $table->unsignedBigInteger('finished_at_ms')->nullable();
            $table->timestamps();
        });

        Schema::create('live_quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_quiz_session_id')->constrained('live_quiz_sessions')->cascadeOnDelete();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('question_index');
            $table->string('selected_answer', 200)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('xp_earned')->default(0);
            $table->unsignedBigInteger('answered_at_ms')->nullable();
            $table->timestamps();
            $table->unique(['live_quiz_session_id', 'student_user_id', 'question_index'], 'uq_live_quiz_answer_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_quiz_answers');
        Schema::dropIfExists('live_quiz_sessions');
        Schema::dropIfExists('live_quiz_questions');
        Schema::dropIfExists('live_quizzes');
    }
};

