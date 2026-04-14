<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_board_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('message_key', 60);
            $table->string('message', 200);
            $table->timestamps();

            $table->index(['school_class_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_board_posts');
    }
};

