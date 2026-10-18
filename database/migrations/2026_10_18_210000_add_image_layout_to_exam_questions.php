<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            if (! Schema::hasColumn('exam_questions', 'image_align')) {
                $table->string('image_align', 10)->default('left')->after('image_scale');
            }
            if (! Schema::hasColumn('exam_questions', 'image_pos')) {
                $table->string('image_pos', 10)->default('below')->after('image_align');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn(['image_align', 'image_pos']);
        });
    }
};
