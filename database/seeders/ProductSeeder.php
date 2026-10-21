<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Warna latar gambar per kategori: [awal, akhir].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const COLORS = [
        'Elektronik' => ['#dfe7fd', '#b9c9f9'],
        'Fashion' => ['#fde0ef', '#f7b8d6'],
        'Aksesori' => ['#e9defa', '#cdb6f0'],
        'Makanan' => ['#ffe8d1', '#ffc999'],
        'Minuman' => ['#efe1d6', '#d9bba3'],
        'Rumah Tangga' => ['#d5f5ec', '#a8e6d2'],
        'Alat Tulis' => ['#fff3cd', '#ffe08a'],
        'Kesehatan & Kecantikan' => ['#d4f1f6', '#a5dfe9'],
        'Olahraga' => ['#d3f6e6', '#a3e8c6'],
        'Mainan' => ['#fddedb', '#f8b5ae'],
    ];

    /**
     * @return array<string, array<int, array{0: string, 1: int, 2: int, 3: string, 4: string}>>
     */
    private function catalog(): array
    {
        // [nama, harga, stok, deskripsi, emoji untuk gambar]
        return [
            'Elektronik' => [
                ['Earphone Bluetooth Nova', 249000, 60, 'Earphone nirkabel dengan baterai tahan hingga 20 jam.', '🎧'],
                ['Power Bank 10.000 mAh', 175000, 45, 'Power bank ringkas dengan dua port USB dan pengisian cepat.', '🔋'],
                ['Speaker Portabel Gema', 329000, 25, 'Speaker bluetooth tahan percikan air dengan suara jernih.', '🔊'],
            ],
            'Fashion' => [
                ['Kaos Polos Katun Combed', 89000, 120, 'Kaos katun combed 30s, nyaman untuk dipakai sehari-hari.', '👕'],
                ['Kemeja Flanel Kotak', 189000, 55, 'Kemeja flanel lengan panjang motif kotak.', '👔'],
                ['Topi Baseball Kanvas', 69000, 80, 'Topi kanvas dengan pengait belakang yang dapat disesuaikan.', '🧢'],
            ],
            'Aksesori' => [
                ['Tas Kanvas Aruna', 189000, 40, 'Tas kanvas serbaguna untuk aktivitas harian.', '👜'],
                ['Dompet Kulit Pria', 159000, 35, 'Dompet kulit sintetis dengan banyak slot kartu.', '👛'],
                ['Jam Tangan Analog Klasik', 325000, 20, 'Jam tangan analog tahan percikan air dengan tali kulit.', '⌚'],
            ],
            'Makanan' => [
                ['Keripik Singkong Pedas 200g', 18000, 200, 'Keripik singkong renyah dengan bumbu pedas manis.', '🍟'],
                ['Rendang Instan 150g', 45000, 90, 'Rendang sapi siap saji dalam kemasan retort.', '🍛'],
                ['Madu Hutan Murni 250ml', 85000, 70, 'Madu hutan asli tanpa campuran gula.', '🍯'],
            ],
            'Minuman' => [
                ['Kopi Gayo 250g', 65000, 100, 'Biji kopi arabika Gayo sangrai medium.', '☕'],
                ['Teh Melati Premium', 32000, 110, 'Teh hijau beraroma melati dalam kemasan 100g.', '🍵'],
                ['Sirup Markisa 500ml', 28000, 85, 'Sirup markisa pekat untuk minuman dingin.', '🧃'],
            ],
            'Rumah Tangga' => [
                ['Lampu Meja Lumen', 235000, 22, 'Lampu meja LED minimalis dengan tiga tingkat cahaya.', '💡'],
                ['Set Panci Anti Lengket', 399000, 18, 'Set tiga panci anti lengket untuk semua jenis kompor.', '🍳'],
                ['Kotak Penyimpanan 20L', 79000, 65, 'Kotak penyimpanan plastik bertumpuk dengan penutup rapat.', '📦'],
            ],
            'Alat Tulis' => [
                ['Buku Tulis Kotak A5', 12000, 300, 'Buku tulis kotak A5, 80 halaman.', '📓'],
                ['Pulpen Gel Hitam (Box 12)', 36000, 150, 'Pulpen gel 0,5 mm tinta hitam, isi 12 buah.', '🖊️'],
                ['Stabilo Warna Pastel (Set 6)', 42000, 90, 'Penanda teks enam warna pastel.', '🖍️'],
            ],
            'Kesehatan & Kecantikan' => [
                ['Sabun Cuci Muka Herbal', 38000, 95, 'Sabun cuci muka lembut berbahan ekstrak herbal.', '🧼'],
                ['Hand Sanitizer 250ml', 22000, 140, 'Hand sanitizer dengan kandungan alkohol 70%.', '🧴'],
                ['Vitamin C 500mg (30 Tablet)', 55000, 75, 'Suplemen vitamin C untuk menjaga daya tahan tubuh.', '💊'],
            ],
            'Olahraga' => [
                ['Matras Yoga 6mm', 135000, 30, 'Matras yoga anti selip dengan tebal 6 mm.', '🧘'],
                ['Botol Minum Olahraga 1L', 59000, 85, 'Botol minum BPA free dengan penutup anti bocor.', '🥤'],
                ['Raket Badminton Alumunium', 149000, 28, 'Raket badminton ringan beserta tas pelindung.', '🏸'],
            ],
            'Mainan' => [
                ['Balok Susun Kayu (50 pcs)', 119000, 24, 'Balok susun kayu warna-warni untuk melatih kreativitas anak.', '🧱'],
                ['Puzzle Peta Indonesia', 75000, 38, 'Puzzle edukatif 100 keping bergambar peta Indonesia.', '🧩'],
                ['Mobil Remote Control Turbo', 215000, 4, 'Mobil remote control dengan baterai isi ulang.', '🏎️'],
            ],
        ];
    }

    public function run(): void
    {
        $number = 1;
        $disk = Storage::disk(Product::IMAGE_DISK);

        foreach ($this->catalog() as $category => $items) {
            foreach ($items as [$name, $price, $stock, $description, $emoji]) {
                $sku = sprintf('SKU-%03d', $number++);

                $product = Product::firstOrCreate(
                    ['sku' => $sku],
                    [
                        'name' => $name,
                        'category' => $category,
                        'price' => $price,
                        'stock' => $stock,
                        'description' => $description,
                    ],
                );

                if (! $product->image) {
                    $path = 'products/'.strtolower($sku).'.svg';

                    $disk->put($path, $this->illustration($emoji, self::COLORS[$category]));
                    $product->update(['image' => $path]);
                }
            }
        }
    }

    /**
     * @param  array{0: string, 1: string}  $colors
     */
    private function illustration(string $emoji, array $colors): string
    {
        [$from, $to] = $colors;

        return <<<SVG
        <?xml version="1.0" encoding="UTF-8"?>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" width="400" height="300">
          <defs>
            <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="{$from}"/>
              <stop offset="1" stop-color="{$to}"/>
            </linearGradient>
          </defs>
          <rect width="400" height="300" fill="url(#bg)"/>
          <circle cx="200" cy="150" r="105" fill="#ffffff" fill-opacity="0.45"/>
          <text x="200" y="195" font-size="130" text-anchor="middle" font-family="Segoe UI Emoji, Apple Color Emoji, Noto Color Emoji, sans-serif">{$emoji}</text>
        </svg>
        SVG;
    }
}
