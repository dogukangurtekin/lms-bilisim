<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_rooms', function (Blueprint $table) {
            // Kodsuz oda: giriş yapan öğrenciler sağ alttaki bildirimle kod girmeden katılır.
            $table->boolean('is_open')->default(false)->after('join_code');
        });
    }

    public function down(): void
    {
        Schema::table('competition_rooms', function (Blueprint $table) {
            $table->dropColumn('is_open');
        });
    }
};
