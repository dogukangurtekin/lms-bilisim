<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Her satır: öğrencinin tek mesajı + öğretmen/adminin tek cevabı.
        // replied_at boşken öğrencinin "bekleyen" mesajı vardır ve yenisini yazamaz.
        Schema::create('student_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('recipient_type', 10); // teacher | admin
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->text('reply_body')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('student_read_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'replied_at']);
            $table->index(['recipient_type', 'recipient_user_id', 'replied_at'], 'student_messages_inbox_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_messages');
    }
};
