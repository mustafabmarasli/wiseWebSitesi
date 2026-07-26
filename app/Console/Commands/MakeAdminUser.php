<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class MakeAdminUser extends Command
{
    protected $signature = 'admin:create
                            {--email= : Yönetici e-postası}
                            {--name= : Ad soyad}';

    protected $description = 'Admin paneline girebilecek bir yönetici hesabı oluşturur veya mevcut hesabı yönetici yapar';

    public function handle(): int
    {
        $email = $this->option('email') ?: text(
            label: 'E-posta adresi',
            required: true,
        );

        $validator = Validator::make(['email' => $email], ['email' => 'required|email']);

        if ($validator->fails()) {
            $this->error('Geçerli bir e-posta adresi girin.');

            return Command::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            if ($existing->is_admin) {
                $this->info("{$email} zaten yönetici.");

                if (!confirm(label: 'Şifresini sıfırlamak ister misiniz?', default: false)) {
                    return Command::SUCCESS;
                }
            } else {
                $this->line("{$email} kayıtlı bir müşteri hesabı.");

                if (!confirm(label: 'Bu hesabı yönetici yapmak ister misiniz?', default: true)) {
                    return Command::SUCCESS;
                }
            }
        }

        // Şifre terminalde gizli okunur; komut geçmişine ya da loglara düşmez.
        $plain = password(
            label: $existing ? 'Yeni şifre' : 'Şifre',
            required: true,
            validate: fn (string $value) => strlen($value) < 8
                ? 'Şifre en az 8 karakter olmalıdır.'
                : null,
        );

        $confirmation = password(label: 'Şifre tekrar', required: true);

        if ($plain !== $confirmation) {
            $this->error('Şifreler eşleşmiyor.');

            return Command::FAILURE;
        }

        if ($existing) {
            $existing->update([
                'password' => Hash::make($plain),
                'is_admin' => true,
            ]);

            $this->info("{$email} güncellendi ve yönetici yapıldı.");
        } else {
            $name = $this->option('name') ?: text(
                label: 'Ad Soyad',
                default: 'Yönetici',
                required: true,
            );

            User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($plain),
                'is_admin' => true,
            ]);

            $this->info("Yönetici hesabı oluşturuldu: {$email}");
        }

        $this->newLine();
        $this->line('Giriş: ' . rtrim(config('app.url'), '/') . '/admin');

        return Command::SUCCESS;
    }
}
