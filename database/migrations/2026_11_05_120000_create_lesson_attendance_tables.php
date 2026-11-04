<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Haftalık ders programı: sınıf + gün + saat aralığı.
        Schema::create('lesson_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');                 // 1 = Pazartesi ... 7 = Pazar
            $table->unsignedTinyInteger('lesson_no')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('subject', 120)->default('Bilişim Teknolojileri');
            $table->foreignId('teacher_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['school_class_id', 'weekday']);
        });

        // Yapılan (ya da yapılması gereken) tek bir ders; program silinse de geçmiş kalsın diye ders bilgisi burada saklanır.
        Schema::create('lesson_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_slot_id')->nullable()->constrained('lesson_slots')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->date('date')->index();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('subject', 120);
            $table->unsignedSmallInteger('total_students')->default(0);
            $table->unsignedSmallInteger('present_count')->default(0);
            $table->unsignedSmallInteger('absent_count')->default(0);
            $table->timestamp('finalized_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['school_class_id', 'start_at']);
        });

        Schema::create('lesson_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_attendance_id')->constrained('lesson_attendances')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('status', 10);                           // present | absent | excused
            $table->dateTime('first_seen_at')->nullable();          // öğrencinin derste sisteme ilk girdiği an
            $table->string('source', 10)->default('auto');          // auto | manual
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['lesson_attendance_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        // Tatil / ders yapılmayan günler: bu günlerde otomatik yoklama alınmaz.
        Schema::create('attendance_off_days', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_off_days');
        Schema::dropIfExists('lesson_attendance_records');
        Schema::dropIfExists('lesson_attendances');
        Schema::dropIfExists('lesson_slots');
    }
};
