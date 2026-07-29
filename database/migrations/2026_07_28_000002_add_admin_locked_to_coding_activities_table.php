<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coding_activities', function (Blueprint $table): void {
            if (! Schema::hasColumn('coding_activities', 'admin_locked')) {
                $table->boolean('admin_locked')->default(false)->after('teacher_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('coding_activities', function (Blueprint $table): void {
            if (Schema::hasColumn('coding_activities', 'admin_locked')) {
                $table->dropColumn('admin_locked');
            }
        });
    }
};
