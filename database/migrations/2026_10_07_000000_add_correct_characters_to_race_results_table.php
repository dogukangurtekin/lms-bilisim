<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_results', function (Blueprint $table): void {
            if (! Schema::hasColumn('race_results', 'correct_characters')) {
                $table->unsignedInteger('correct_characters')->default(0)->after('accuracy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('race_results', function (Blueprint $table): void {
            if (Schema::hasColumn('race_results', 'correct_characters')) {
                $table->dropColumn('correct_characters');
            }
        });
    }
};
