<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserIfMissing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user-if-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a default admin user if it does not exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = 'admin@school.local';
        $password = 'Admin123!';

        $adminRole = Role::query()->where('slug', 'admin')->first();
        if (! $adminRole) {
            $this->error('Admin rolü bulunamadı (roles.slug=admin).');
            return self::FAILURE;
        }

        $existing = User::query()->whereRaw('LOWER(email)=?', [strtolower($email)])->first();
        if ($existing) {
            $existing->role_id = $adminRole->id;
            $existing->is_active = true;
            $existing->password = $password;
            $existing->save();
            $this->info("Mevcut kullanıcı güncellendi: {$email}");
            $this->line("Şifre: {$password}");
            return self::SUCCESS;
        }

        User::query()->create([
            'role_id' => $adminRole->id,
            'name' => 'Sistem Yöneticisi',
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $this->info("Admin kullanıcı oluşturuldu: {$email}");
        $this->line("Şifre: {$password}");
        return self::SUCCESS;
    }
}
