<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_time_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedBigInteger('total_seconds')->default(0);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_time_stats');
    }
};

