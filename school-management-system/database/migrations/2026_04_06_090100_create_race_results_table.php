<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 60);
            $table->decimal('progress', 5, 2)->unsigned()->default(0);
            $table->decimal('wpm', 6, 2)->unsigned()->default(0);
            $table->decimal('accuracy', 5, 2)->unsigned()->default(100);
            $table->boolean('is_spectator')->default(false);
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'is_spectator']);
            $table->index(['room_id', 'progress', 'wpm']);
            $table->unique(['room_id', 'user_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_results');
    }
};
