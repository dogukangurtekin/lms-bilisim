<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('game_slug', 100)->index();
            $table->string('game_name', 150);
            $table->string('title', 150);
            $table->date('due_date')->nullable();
            $table->unsignedInteger('level_from')->nullable();
            $table->unsignedInteger('level_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('game_assignment_school_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_assignment_id')->constrained('game_assignments')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->unique(['game_assignment_id', 'school_class_id'], 'ga_sc_unique');
        });

        Schema::create('game_assignment_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_assignment_id')->constrained('game_assignments')->cascadeOnDelete();
            $table->unsignedInteger('level');
            $table->unsignedInteger('points')->default(0);
            $table->unique(['game_assignment_id', 'level'], 'ga_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_assignment_levels');
        Schema::dropIfExists('game_assignment_school_class');
        Schema::dropIfExists('game_assignments');
    }
};

