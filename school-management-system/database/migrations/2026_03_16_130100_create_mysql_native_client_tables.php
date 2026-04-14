<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('username')->nullable()->index();
            $table->string('role')->default('student')->index();
            $table->integer('xp')->default(0)->index();
            $table->integer('total_time_seconds')->default(0);
            $table->string('selected_avatar_id')->nullable();
            $table->string('class_name')->nullable()->index();
            $table->string('section')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('game_states', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('student_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->integer('total_xp')->default(0)->index();
            $table->integer('total_duration_ms')->default(0);
            $table->decimal('completion_percent', 6, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('book_task_progresses', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->boolean('completed')->default(false)->index();
            $table->boolean('approved')->default(false)->index();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['task_id', 'user_id']);
        });

        Schema::create('content_progresses', function (Blueprint $table) {
            $table->id();
            $table->string('content_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->boolean('completed')->default(false)->index();
            $table->integer('xp_awarded')->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['content_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_progresses');
        Schema::dropIfExists('book_task_progresses');
        Schema::dropIfExists('student_reports');
        Schema::dropIfExists('game_states');
        Schema::dropIfExists('user_profiles');
    }
};

