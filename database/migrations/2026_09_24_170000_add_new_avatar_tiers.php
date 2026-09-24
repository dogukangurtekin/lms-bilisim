<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $avatars = [
            ['name' => 'Kristal Gezgin', 'image_path' => 'avatars/store/avatar-11.svg', 'required_xp' => 1100],
            ['name' => 'Ejderha Kodlayıcı', 'image_path' => 'avatars/store/avatar-12.svg', 'required_xp' => 1350],
            ['name' => 'Yıldız Amirali', 'image_path' => 'avatars/store/avatar-13.svg', 'required_xp' => 1650],
            ['name' => 'Kuantum Usta', 'image_path' => 'avatars/store/avatar-14.svg', 'required_xp' => 2000],
            ['name' => 'Efsanevi İmparator', 'image_path' => 'avatars/store/avatar-15.svg', 'required_xp' => 2500],
        ];

        foreach ($avatars as $avatar) {
            $exists = DB::table('avatars')->where('name', $avatar['name'])->exists();
            if ($exists) {
                continue;
            }
            DB::table('avatars')->insert([
                'name' => $avatar['name'],
                'image_path' => $avatar['image_path'],
                'required_xp' => $avatar['required_xp'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('avatars')->whereIn('name', [
            'Kristal Gezgin',
            'Ejderha Kodlayıcı',
            'Yıldız Amirali',
            'Kuantum Usta',
            'Efsanevi İmparator',
        ])->delete();
    }
};
