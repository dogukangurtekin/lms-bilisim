<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'description' => 'System administrator']);
        Role::firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        Role::firstOrCreate(['slug' => 'student'], ['name' => 'Student']);

        User::firstOrCreate(
            ['email' => 'admin@school.local'],
            [
                'role_id' => $adminRole->id,
                'name' => 'System Admin',
                'password' => Hash::make('Admin1234!'),
                'is_active' => true,
            ]
        );
    }
}
