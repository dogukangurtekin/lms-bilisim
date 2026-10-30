<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['course_homeworks', 'game_assignments'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('is_public')->default(false);
                // Aynı anda birden çok sınıfa verilen ödevler tek linki paylaşır; bu yüzden unique değil.
                $t->string('public_token', 40)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['course_homeworks', 'game_assignments'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['public_token']);
                $t->dropColumn(['is_public', 'public_token']);
            });
        }
    }
};
