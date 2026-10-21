<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['Budi Santoso', 'budi.santoso@example.test', '081234567801', 'Jl. Merdeka No. 12, Jakarta Pusat'],
            ['Siti Aminah', 'siti.aminah@example.test', '081234567802', 'Jl. Dago Atas No. 45, Bandung'],
            ['Agus Prasetyo', 'agus.prasetyo@example.test', '081234567803', 'Jl. Pemuda No. 8, Semarang'],
            ['Dewi Lestari', 'dewi.lestari@example.test', '081234567804', 'Jl. Malioboro No. 21, Yogyakarta'],
            ['Rizky Ramadhan', 'rizky.ramadhan@example.test', '081234567805', 'Jl. Ahmad Yani No. 99, Surabaya'],
            ['Putri Maharani', 'putri.maharani@example.test', '081234567806', 'Jl. Gajah Mada No. 7, Medan'],
            ['Hendra Wijaya', 'hendra.wijaya@example.test', '081234567807', 'Jl. Sudirman No. 150, Palembang'],
            ['Maya Anggraini', 'maya.anggraini@example.test', '081234567808', 'Jl. Diponegoro No. 33, Denpasar'],
            ['Fajar Nugroho', 'fajar.nugroho@example.test', '081234567809', 'Jl. Veteran No. 5, Malang'],
            ['Lina Marlina', 'lina.marlina@example.test', '081234567810', 'Jl. Pahlawan No. 17, Bekasi'],
            ['Yoga Pratama', 'yoga.pratama@example.test', '081234567811', 'Jl. Cendana No. 3, Makassar'],
            ['Nur Hidayah', 'nur.hidayah@example.test', '081234567812', 'Jl. Kenanga No. 28, Balikpapan'],
            ['Toko Sumber Rejeki', 'sumber.rejeki@example.test', '0215550123', 'Pasar Baru Blok C No. 14, Jakarta'],
            ['CV Maju Bersama', 'maju.bersama@example.test', '0225550456', 'Jl. Soekarno-Hatta No. 210, Bandung'],
            ['Wahyu Setiawan', 'wahyu.setiawan@example.test', '081234567815', null],
        ];

        foreach ($customers as [$name, $email, $phone, $address]) {
            Customer::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'phone' => $phone, 'address' => $address],
            );
        }
    }
}
