<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_tracks', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_track_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('order_no')->default(1);
            $table->json('content')->nullable();
            $table->timestamps();
            $table->index(['learning_track_id', 'order_no']);
        });

        Schema::create('coding_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['daily_task', 'quiz', 'race', 'live_quiz'])->index();
            $table->string('title');
            $table->text('instruction')->nullable();
            $table->unsignedInteger('base_xp')->default(10);
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_bonus')->default(false)->index();
            $table->date('active_on')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['type', 'active_on']);
        });

        Schema::create('activity_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coding_activity_id')->constrained()->cascadeOnDelete();
            $table->enum('question_type', ['single_choice', 'multi_choice', 'short_text', 'code_output'])->index();
            $table->text('prompt');
            $table->json('answer_key')->nullable();
            $table->unsignedInteger('points')->default(10);
            $table->unsignedInteger('order_no')->default(1);
            $table->timestamps();
            $table->index(['coding_activity_id', 'order_no']);
        });

        Schema::create('question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_question_id')->constrained()->cascadeOnDelete();
            $table->string('option_key', 20);
            $table->text('label');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('order_no')->default(1);
            $table->timestamps();
            $table->index(['activity_question_id', 'order_no']);
        });

        Schema::create('activity_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coding_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['started', 'submitted', 'finished'])->default('started')->index();
            $table->unsignedInteger('score')->default(0)->index();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('penalty')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'coding_activity_id', 'created_at']);
        });

        Schema::create('attempt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_question_id')->constrained()->cascadeOnDelete();
            $table->json('answer_payload')->nullable();
            $table->decimal('awarded_points', 8, 2)->default(0);
            $table->timestamps();
            $table->unique(['activity_attempt_id', 'activity_question_id']);
        });

        Schema::create('leaderboards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coding_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0)->index();
            $table->unsignedInteger('duration_seconds')->default(0)->index();
            $table->unsignedInteger('rank_no')->nullable()->index();
            $table->timestamps();
            $table->unique(['coding_activity_id', 'user_id']);
        });

        Schema::create('coding_live_quiz_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coding_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('pin', 6)->unique();
            $table->enum('status', ['waiting', 'running', 'finished'])->default('waiting')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('coding_live_quiz_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_quiz_room_id')->constrained('coding_live_quiz_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0)->index();
            $table->unsignedInteger('total_duration_seconds')->default(0);
            $table->timestamps();
            $table->unique(['live_quiz_room_id', 'user_id']);
        });

        Schema::create('coding_live_quiz_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_quiz_room_id')->constrained('coding_live_quiz_rooms')->cascadeOnDelete();
            $table->string('event_type');
            $table->json('payload')->nullable();
            $table->timestamp('event_at')->useCurrent()->index();
            $table->timestamps();
        });

        Schema::create('user_streaks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('best_streak')->default(0);
            $table->date('last_activity_date')->nullable()->index();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('user_xp_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coding_activity_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('xp_delta');
            $table->string('reason');
            $table->date('awarded_on')->index();
            $table->timestamps();
            $table->index(['user_id', 'awarded_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_xp_logs');
        Schema::dropIfExists('user_streaks');
        Schema::dropIfExists('coding_live_quiz_events');
        Schema::dropIfExists('coding_live_quiz_participants');
        Schema::dropIfExists('coding_live_quiz_rooms');
        Schema::dropIfExists('leaderboards');
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('activity_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('activity_questions');
        Schema::dropIfExists('coding_activities');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('learning_tracks');
    }
};


