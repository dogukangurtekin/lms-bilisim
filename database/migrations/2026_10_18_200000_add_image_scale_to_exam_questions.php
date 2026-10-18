<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('exam_questions', 'image_scale')) {
            Schema::table('exam_questions', function (Blueprint $table) {
                $table->unsignedSmallInteger('image_scale')->default(100)->after('image_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exam_questions', 'image_scale')) {
            Schema::table('exam_questions', function (Blueprint $table) {
                $table->dropColumn('image_scale');
            });
        }
    }
};
