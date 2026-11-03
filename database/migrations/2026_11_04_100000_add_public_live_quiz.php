<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('live_quiz_sessions', 'is_public')) {
            Schema::table('live_quiz_sessions', function (Blueprint $table) {
                $table->boolean('is_public')->default(false)->after('between_plan');
            });
        }

        // Giriş yapmadan kod + ad soyad ile katılan ziyaretçiler bu rolde, oturuma özel geçici hesaplardır.
        Role::query()->firstOrCreate(['slug' => 'guest'], ['name' => 'Misafir']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('live_quiz_sessions', 'is_public')) {
            Schema::table('live_quiz_sessions', function (Blueprint $table) {
                $table->dropColumn('is_public');
            });
        }
    }
};
