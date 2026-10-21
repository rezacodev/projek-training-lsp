<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Aman dijalankan berulang kali: data master memakai firstOrCreate dan
     * transaksi hanya dibuat jika belum ada transaksi sama sekali.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
