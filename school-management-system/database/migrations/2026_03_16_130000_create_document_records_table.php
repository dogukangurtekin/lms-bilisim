<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_records', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('collection_name')->index();
            $table->string('document_id')->index();
            $table->string('parent_path')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_records');
    }
};

