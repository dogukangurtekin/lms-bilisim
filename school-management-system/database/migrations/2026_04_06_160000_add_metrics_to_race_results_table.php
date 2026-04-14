<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_results', function (Blueprint $table): void {
            if (! Schema::hasColumn('race_results', 'elapsed_seconds')) {
                $table->unsignedInteger('elapsed_seconds')->default(0)->after('accuracy');
            }
            if (! Schema::hasColumn('race_results', 'completion_seconds')) {
                $table->unsignedInteger('completion_seconds')->nullable()->after('elapsed_seconds');
            }
            if (! Schema::hasColumn('race_results', 'xp_earned')) {
                $table->unsignedInteger('xp_earned')->default(0)->after('completion_seconds');
            }
        });
    }

    public function down(): void
    {
        Schema::table('race_results', function (Blueprint $table): void {
            foreach (['xp_earned', 'completion_seconds', 'elapsed_seconds'] as $column) {
                if (Schema::hasColumn('race_results', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

