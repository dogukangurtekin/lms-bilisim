<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_game_assignment_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_assignment_id')->constrained('game_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('xp_awarded')->default(0);
            $table->unsignedInteger('level_from')->nullable();
            $table->unsignedInteger('level_to')->nullable();
            $table->unsignedInteger('reached_level')->nullable();
            $table->unsignedInteger('completion_seconds')->default(0);
            $table->json('completion_payload')->nullable();
            $table->timestamps();
            $table->unique(['game_assignment_id', 'student_id'], 'sgap_assignment_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_game_assignment_progresses');
    }
};

