<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Öğrencinin tamamlanmamış bir derste kaldığı yer. Tamamlanma kayıtları
        // (content_progresses) ve raporlar bundan etkilenmesin diye ayrı tutulur.
        Schema::create('student_course_resumes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedSmallInteger('slide_index')->default(0);
            $table->json('state')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_course_resumes');
    }
};
