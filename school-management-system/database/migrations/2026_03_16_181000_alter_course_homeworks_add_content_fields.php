<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_homeworks', function (Blueprint $table) {
            $table->string('assignment_type', 20)->default('lesson')->after('school_class_id');
            $table->string('target_slug', 120)->nullable()->after('assignment_type');
            $table->unsignedInteger('level_from')->nullable()->after('due_date');
            $table->unsignedInteger('level_to')->nullable()->after('level_from');
        });
    }

    public function down(): void
    {
        Schema::table('course_homeworks', function (Blueprint $table) {
            $table->dropColumn(['assignment_type', 'target_slug', 'level_from', 'level_to']);
        });
    }
};

