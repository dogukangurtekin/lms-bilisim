<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdminUserIfMissing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user-if-missing {--force : Mevcut admin@school.local hesabinin sifresini yeni rastgele bir sifreyle sifirla}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a default admin user if it does not exist (never overwrites an existing account unless --force is passed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = 'admin@school.local';

        $adminRole = Role::query()->where('slug', 'admin')->first();
        if (! $adminRole) {
            $this->error('Admin rolü bulunamadı (roles.slug=admin).');
            return self::FAILURE;
        }

        $existing = User::query()->whereRaw('LOWER(email)=?', [strtolower($email)])->first();
        if ($existing) {
            if (! $this->option('force')) {
                // Guvenlik: bu komut sabit/bilinen bir sifreyle mevcut admin
                // hesabini sessizce ezmemeli - bu, sunucuya erisimi olan herkese
                // (veya ileride bulunabilecek bir komut calistirma acigina)
                // hazir bir arka kapi sunar. Kasitli bir sifirlama isteniyorsa
                // --force ile acikca belirtilmeli.
                $this->info("Admin kullanıcı zaten mevcut: {$email} (şifre değiştirilmedi). Sıfırlamak için --force kullanın.");
                return self::SUCCESS;
            }

            $newPassword = Str::password(20);
            $existing->role_id = $adminRole->id;
            $existing->is_active = true;
            $existing->password = $newPassword;
            $existing->save();
            $this->info("Mevcut kullanıcı güncellendi: {$email}");
            $this->line("Yeni şifre (sadece bir kez gösterilir): {$newPassword}");
            return self::SUCCESS;
        }

        $newPassword = Str::password(20);
        User::query()->create([
            'role_id' => $adminRole->id,
            'name' => 'Sistem Yöneticisi',
            'email' => $email,
            'password' => $newPassword,
            'is_active' => true,
        ]);

        $this->info("Admin kullanıcı oluşturuldu: {$email}");
        $this->line("Şifre (sadece bir kez gösterilir, kaydedin): {$newPassword}");
        return self::SUCCESS;
    }
}
