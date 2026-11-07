<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SoohouseInstallCommand extends Command
{
    protected $signature = 'soohouse:install
                            {--email= : Süper Admin e-posta adresi}
                            {--password= : Süper Admin şifresi}
                            {--name=Soohouse Süper Admin : Süper Admin ad soyadı}';

    protected $description = 'Soohouse Coffee varsayılan rollerini, izinlerini, temel ayarlarını ve Süper Admin hesabını kurar (sahte veri içermez).';

    public function handle(): int
    {
        $this->info('Soohouse Coffee® Kurulumu Başlatılıyor...');

        $seeder = new DatabaseSeeder;
        $seeder->run();

        $email = $this->option('email') ?: env('SUPER_ADMIN_EMAIL');
        $password = $this->option('password') ?: env('SUPER_ADMIN_PASSWORD');
        $name = $this->option('name') ?: env('SUPER_ADMIN_NAME', 'Soohouse Süper Admin');

        if (! $email && $this->input->isInteractive()) {
            $email = $this->ask('Süper Admin e-posta adresini girin', 'admin@soohousecoffee.com');
        }

        if (! $password && $this->input->isInteractive()) {
            $password = $this->secret('Süper Admin şifresini girin (en az 8 karakter)');
        }

        if ($email && $password) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'all_branches' => true,
                ]
            );

            $superRole = Role::where('slug', 'super_admin')->first();
            if ($superRole) {
                $user->roles()->syncWithoutDetaching([$superRole->id]);
            }

            $this->info("Süper Admin hesabı hazır: {$email}");
        } else {
            $this->warn('SUPER_ADMIN_EMAIL veya SUPER_ADMIN_PASSWORD belirtilmediği için Süper Admin oluşturulmadı. .env dosyasına ekleyip tekrar çalıştırabilir veya --email / --password parametrelerini kullanabilirsiniz.');
        }

        $this->info('Kurulum başarıyla tamamlandı. Veritabanı temiz (demo/sahte veri olmadan) teslim edildi.');

        return self::SUCCESS;
    }
}
