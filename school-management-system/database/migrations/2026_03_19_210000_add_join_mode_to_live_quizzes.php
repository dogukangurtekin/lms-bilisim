<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('live_quizzes', 'join_mode')) {
                $table->string('join_mode', 20)->default('code')->after('school_class_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('live_quizzes', 'join_mode')) {
                $table->dropColumn('join_mode');
            }
        });
    }
};

