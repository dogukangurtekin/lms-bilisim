<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function avatars(): array
    {
        return [
            ['name' => 'Siber Samuray', 'image_path' => 'avatars/store/avatar-16.svg', 'required_xp' => 1500],
            ['name' => 'Uzay Komutanı', 'image_path' => 'avatars/store/avatar-17.svg', 'required_xp' => 1800],
            ['name' => 'Gece Hacker', 'image_path' => 'avatars/store/avatar-18.svg', 'required_xp' => 2200],
            ['name' => 'Büyücü Kodlayıcı', 'image_path' => 'avatars/store/avatar-19.svg', 'required_xp' => 2800],
            ['name' => 'Altın Şövalye', 'image_path' => 'avatars/store/avatar-20.svg', 'required_xp' => 3200],
            ['name' => 'Jet Pilotu', 'image_path' => 'avatars/store/avatar-21.svg', 'required_xp' => 3800],
            ['name' => 'Android X', 'image_path' => 'avatars/store/avatar-22.svg', 'required_xp' => 4500],
            ['name' => 'Şampiyon İmparator', 'image_path' => 'avatars/store/avatar-23.svg', 'required_xp' => 5500],
        ];
    }

    public function up(): void
    {
        $now = now();
        foreach ($this->avatars() as $avatar) {
            if (DB::table('avatars')->where('name', $avatar['name'])->exists()) {
                continue;
            }
            DB::table('avatars')->insert($avatar + [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('avatars')->whereIn('name', array_column($this->avatars(), 'name'))->delete();
    }
};
