<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'avatar_xp_spent')) {
                $table->unsignedInteger('avatar_xp_spent')->default(0)->after('current_avatar_id');
            }
        });

        $now = now();
        $avatars = [
            ['name' => 'Robot Mavi', 'image_path' => 'avatars/store/avatar-1.svg', 'required_xp' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Robot Yesil', 'image_path' => 'avatars/store/avatar-2.svg', 'required_xp' => 60, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Robot Turuncu', 'image_path' => 'avatars/store/avatar-3.svg', 'required_xp' => 120, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Astronot Mini', 'image_path' => 'avatars/store/avatar-4.svg', 'required_xp' => 180, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kod Ninja', 'image_path' => 'avatars/store/avatar-5.svg', 'required_xp' => 240, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Uzay Pilotu', 'image_path' => 'avatars/store/avatar-6.svg', 'required_xp' => 320, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Siber Kaşif', 'image_path' => 'avatars/store/avatar-7.svg', 'required_xp' => 400, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Neo Coder', 'image_path' => 'avatars/store/avatar-8.svg', 'required_xp' => 520, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Galaksi Ustasi', 'image_path' => 'avatars/store/avatar-9.svg', 'required_xp' => 700, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Efsane Mimar', 'image_path' => 'avatars/store/avatar-10.svg', 'required_xp' => 900, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($avatars as $avatar) {
            $exists = DB::table('avatars')->where('name', $avatar['name'])->exists();
            if (! $exists) {
                DB::table('avatars')->insert($avatar);
            }
        }
    }

    public function down(): void
    {
        DB::table('avatars')->whereIn('name', [
            'Robot Mavi', 'Robot Yesil', 'Robot Turuncu', 'Astronot Mini', 'Kod Ninja',
            'Uzay Pilotu', 'Siber Kaşif', 'Neo Coder', 'Galaksi Ustasi', 'Efsane Mimar',
        ])->delete();

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'avatar_xp_spent')) {
                $table->dropColumn('avatar_xp_spent');
            }
        });
    }
};

