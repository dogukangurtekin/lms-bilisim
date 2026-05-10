<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Eski FK (cascade) kaldirilir, kolon nullable yapilir ve nullOnDelete FK eklenir.
            $table->dropForeign(['school_class_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('school_class_id')->nullable()->change();
            $table->foreign('school_class_id')
                ->references('id')
                ->on('school_classes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['school_class_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('school_class_id')->nullable(false)->change();
            $table->foreign('school_class_id')
                ->references('id')
                ->on('school_classes')
                ->cascadeOnDelete();
        });
    }
};

