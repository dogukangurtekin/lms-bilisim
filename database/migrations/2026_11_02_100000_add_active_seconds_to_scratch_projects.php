<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scratch_projects', function (Blueprint $t) {
            $t->unsignedInteger('active_seconds')->default(0)->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('scratch_projects', function (Blueprint $t) {
            $t->dropColumn('active_seconds');
        });
    }
};
