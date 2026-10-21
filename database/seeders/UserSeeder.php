<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun contoh untuk pengembangan lokal. Kata sandi: "password".
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'email' => 'admin@example.com'],
            ['name' => 'Rina Kasir', 'email' => 'kasir@example.com'],
            ['name' => 'Dewi Gudang', 'email' => 'gudang@example.com'],
            ['name' => 'Test User', 'email' => 'test@example.com'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                ['name' => $user['name'], 'password' => 'password', 'email_verified_at' => now()],
            );
        }
    }
}
