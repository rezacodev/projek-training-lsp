<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Database\Seeder;
use Illuminate\Validation\ValidationException;

class TransactionSeeder extends Seeder
{
    private const TRANSACTION_COUNT = 45;

    private const NOTES = [
        'Pesanan diambil langsung di toko.',
        'Mohon dibungkus rapi.',
        'Pelanggan langganan.',
        'Dikirim melalui kurir internal.',
        'Pembelian untuk hadiah.',
    ];

    public function run(TransactionService $service): void
    {
        // Transaksi mengurangi stok, jadi hanya dibuat sekali agar data tetap konsisten.
        if (Transaction::query()->exists()) {
            return;
        }

        $products = Product::query()->where('sku', 'like', 'SKU-%')->where('stock', '>', 0)->get();
        $customers = Customer::query()->get();

        if ($products->isEmpty() || $customers->isEmpty()) {
            return;
        }

        mt_srand(20261020);

        $stock = $products->pluck('stock', 'id')->all();
        $days = array_map(fn () => mt_rand(0, 29), range(1, self::TRANSACTION_COUNT));
        rsort($days);

        foreach ($days as $daysAgo) {
            $items = $this->buildItems($products->all(), $stock);

            if ($items === []) {
                continue;
            }

            $payload = $this->buildPayload($items, $customers->all(), $daysAgo);

            try {
                $transaction = $service->create($payload);
            } catch (ValidationException) {
                continue;
            }

            foreach ($items as $item) {
                $stock[$item['product_id']] -= $item['quantity'];
            }

            $moment = now()->subDays($daysAgo)->setTime(mt_rand(8, 20), mt_rand(0, 59));
            $transaction->forceFill(['created_at' => $moment, 'updated_at' => $moment])->saveQuietly();
        }

        mt_srand();
    }

    /**
     * @param  array<int, Product>  $products
     * @param  array<int, int>  $stock
     * @return array<int, array{product_id: int, quantity: int}>
     */
    private function buildItems(array $products, array $stock): array
    {
        $indexes = array_keys($products);

        for ($i = count($indexes) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$indexes[$i], $indexes[$j]] = [$indexes[$j], $indexes[$i]];
        }

        $items = [];

        foreach (array_slice($indexes, 0, mt_rand(1, 5)) as $index) {
            $product = $products[$index];
            $available = $stock[$product->id] ?? 0;

            if ($available < 1) {
                continue;
            }

            $items[] = ['product_id' => $product->id, 'quantity' => min(mt_rand(1, 4), $available)];
        }

        return $items;
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @param  array<int, Customer>  $customers
     * @return array<string, mixed>
     */
    private function buildPayload(array $items, array $customers, int $daysAgo): array
    {
        $prices = Product::query()->whereIn('id', array_column($items, 'product_id'))->pluck('price', 'id');
        $subtotal = array_sum(array_map(fn ($item) => (float) $prices[$item['product_id']] * $item['quantity'], $items));

        $discountType = 'amount';
        $discountValue = 0;
        $roll = mt_rand(1, 100);

        if ($roll <= 20) {
            $discountType = 'percent';
            $discountValue = [5, 10, 15][mt_rand(0, 2)];
        } elseif ($roll <= 30) {
            $discountValue = max(1000, (int) floor($subtotal * 0.1 / 1000) * 1000);
        }

        $taxPercent = mt_rand(1, 100) <= 40 ? 11 : 0;

        $discountAmount = $discountType === 'percent' ? round($subtotal * $discountValue / 100, 2) : (float) $discountValue;
        $taxable = $subtotal - $discountAmount;
        $total = round($taxable + round($taxable * $taxPercent / 100, 2), 2);

        [$method, $paid, $walkIn] = $this->payment($total);

        return [
            'customer_id' => $walkIn ? null : $customers[mt_rand(0, count($customers) - 1)]->id,
            'transaction_date' => now()->subDays($daysAgo)->format('Y-m-d'),
            'notes' => mt_rand(1, 100) <= 25 ? self::NOTES[mt_rand(0, count(self::NOTES) - 1)] : null,
            'items' => $items,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'tax_percent' => $taxPercent,
            'payment_method' => $method,
            'paid_amount' => $paid,
        ];
    }

    /**
     * @return array{0: string, 1: float, 2: bool}
     */
    private function payment(float $total): array
    {
        $roll = mt_rand(1, 100);

        if ($roll <= 45) {
            $step = [1000, 5000, 50000, 100000][mt_rand(0, 3)];

            return ['cash', (float) (ceil($total / $step) * $step), mt_rand(1, 100) <= 35];
        }

        if ($roll <= 88) {
            return [['transfer', 'qris', 'debit'][mt_rand(0, 2)], $total, mt_rand(1, 100) <= 25];
        }

        if ($roll <= 95 && $total >= 10000) {
            $partial = (float) (round($total * mt_rand(30, 70) / 100 / 1000) * 1000);

            return [['cash', 'transfer'][mt_rand(0, 1)], min(max($partial, 1000), $total - 1000), false];
        }

        return ['cash', 0.0, false];
    }
}
