<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coding_activities', function (Blueprint $table): void {
            if (! Schema::hasColumn('coding_activities', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('created_by')->constrained('teachers')->nullOnDelete()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('coding_activities', function (Blueprint $table): void {
            if (Schema::hasColumn('coding_activities', 'teacher_id')) {
                $table->dropConstrainedForeignId('teacher_id');
            }
        });
    }
};
