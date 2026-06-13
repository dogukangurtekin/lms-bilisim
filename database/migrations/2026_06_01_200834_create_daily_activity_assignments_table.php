<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_activity_assignments', function (Blueprint $table): void {
            $table->id();
            $table->date('assignment_date')->index();
            $table->foreignId('coding_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->enum('target_role', ['student'])->default('student')->index();
            $table->timestamps();
            $table->unique(['assignment_date','target_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_activity_assignments');
    }
};
