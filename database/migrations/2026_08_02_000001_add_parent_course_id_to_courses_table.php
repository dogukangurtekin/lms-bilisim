<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('parent_course_id')
                ->nullable()
                ->after('id')
                ->constrained('courses')
                ->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('weekly_hours');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_course_id');
            $table->dropColumn(['sort_order', 'is_active']);
        });
    }
};
