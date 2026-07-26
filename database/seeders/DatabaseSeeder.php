<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ShopSeeder::class);

        // Varsayılan yönetici hesabı
        User::updateOrCreate(
            ['email' => 'info@wisesolutionsa.com.tr'],
            [
                'name'      => 'Wise Solutions Admin',
                'password'  => \Illuminate\Support\Facades\Hash::make('wiseadmin123'),
                'is_admin'  => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'mustafabmarasli@gmail.com'],
            [
                'name'      => 'Mustafa B. Maraşlı',
                'password'  => \Illuminate\Support\Facades\Hash::make('wiseadmin123'),
                'is_admin'  => true,
            ]
        );
    }
}
