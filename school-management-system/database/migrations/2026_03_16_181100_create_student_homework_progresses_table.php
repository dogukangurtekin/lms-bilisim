<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_homework_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_homework_id')->constrained('course_homeworks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('xp_awarded')->default(0);
            $table->timestamps();
            $table->unique(['course_homework_id', 'student_id'], 'shp_homework_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_homework_progresses');
    }
};

