<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_device_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('endpoint', 512)->nullable();
            $table->string('permission', 20)->default('default')->index();
            $table->string('platform', 80)->nullable();
            $table->boolean('is_pwa')->default(false);
            $table->text('user_agent')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['endpoint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_device_statuses');
    }
};


