<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('url', 500);
            $table->string('route_name', 200)->nullable();
            $table->string('action_label', 300)->nullable(); // okunabilir açıklama
            $table->string('method', 10)->default('GET');
            $table->string('ip', 45)->nullable();
            $table->timestamp('logged_at')->useCurrent();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index(['student_id', 'logged_at']);
            $table->index('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activity_logs');
    }
};
