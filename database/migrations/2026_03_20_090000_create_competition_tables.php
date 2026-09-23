<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('game_slug', 60);
            $table->string('game_name', 120);
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->unsignedInteger('level_from')->default(1);
            $table->unsignedInteger('level_to')->default(2);
            $table->unsignedInteger('duration_seconds')->default(300);
            $table->string('join_code', 12)->unique();
            $table->string('status', 20)->default('lobby')->index(); // lobby | live | finished
            $table->unsignedBigInteger('started_at_ms')->nullable();
            $table->unsignedBigInteger('ends_at_ms')->nullable();
            $table->unsignedBigInteger('finished_at_ms')->nullable();
            $table->timestamps();
        });

        Schema::create('competition_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_room_id')->constrained('competition_rooms')->cascadeOnDelete();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('user_name', 150);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->unsignedInteger('current_level_index')->default(0);
            $table->unsignedInteger('xp_earned')->default(0);
            $table->boolean('is_spectator')->default(false);
            $table->unsignedBigInteger('joined_at_ms')->nullable();
            $table->unsignedBigInteger('finished_at_ms')->nullable();
            $table->timestamps();
            $table->unique(['competition_room_id', 'student_user_id'], 'uq_competition_participant_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_participants');
        Schema::dropIfExists('competition_rooms');
    }
};
