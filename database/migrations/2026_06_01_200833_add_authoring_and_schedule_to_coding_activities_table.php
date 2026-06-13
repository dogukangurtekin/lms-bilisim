<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coding_activities', function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->after('lesson_id')->constrained('users')->nullOnDelete();
            $table->json('lesson_pages')->nullable()->after('instruction');
            $table->boolean('is_random_pool')->default(true)->after('is_active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('coding_activities', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['lesson_pages','is_random_pool']);
        });
    }
};
