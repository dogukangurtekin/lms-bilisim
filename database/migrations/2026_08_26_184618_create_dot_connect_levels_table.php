<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dot_connect_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('grid_size')->default(4);
            $table->string('name')->nullable();
            $table->json('target_dots');
            $table->json('start_point');
            $table->string('start_direction', 10)->default('right');
            $table->json('allowed_commands');
            $table->unsignedInteger('max_commands')->default(10);
            $table->unsignedInteger('xp')->default(10);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dot_connect_levels');
    }
};
