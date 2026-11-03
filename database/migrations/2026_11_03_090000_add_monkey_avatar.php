<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NAME = 'Kral Maymun';

    public function up(): void
    {
        if (DB::table('avatars')->where('name', self::NAME)->exists()) {
            return;
        }
        // Mağazadaki en pahalı avatar olsun: mevcut en yüksek fiyatın üstünde (en az 7500 XP).
        $price = max(7500, (int) DB::table('avatars')->max('required_xp') + 500);
        $now = now();
        DB::table('avatars')->insert([
            'name' => self::NAME,
            'image_path' => 'avatars/store/avatar-24.svg',
            'required_xp' => $price,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('avatars')->where('name', self::NAME)->delete();
    }
};
