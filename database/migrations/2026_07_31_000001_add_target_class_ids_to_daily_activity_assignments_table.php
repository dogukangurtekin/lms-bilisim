<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_activity_assignments', function (Blueprint $table): void {
            if (! Schema::hasColumn('daily_activity_assignments', 'target_class_ids')) {
                $table->json('target_class_ids')->nullable()->after('target_role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_activity_assignments', function (Blueprint $table): void {
            if (Schema::hasColumn('daily_activity_assignments', 'target_class_ids')) {
                $table->dropColumn('target_class_ids');
            }
        });
    }
};
