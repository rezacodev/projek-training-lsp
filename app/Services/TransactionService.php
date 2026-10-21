<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create([
                'code' => $this->generateCode(),
                'customer_id' => $data['customer_id'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->apply($transaction, $data);

            return $transaction;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $this->restoreStock($transaction);

            $transaction->update([
                'customer_id' => $data['customer_id'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->apply($transaction, $data);

            return $transaction;
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->restoreStock($transaction);
            $transaction->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function apply(Transaction $transaction, array $data): void
    {
        $products = Product::query()
            ->whereIn('id', collect($data['items'])->pluck('product_id')->map(fn ($id) => (int) $id))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $stockErrors = [];
        $pivotData = [];
        $subtotal = 0.0;

        foreach ($data['items'] as $index => $item) {
            $product = $products->get((int) $item['product_id']);
            $quantity = (int) $item['quantity'];

            if ($product->stock < $quantity) {
                $stockErrors["items.{$index}.quantity"] = "Stok {$product->name} tidak mencukupi (tersisa {$product->stock} unit).";

                continue;
            }

            $unitPrice = (float) $product->price;
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineTotal;

            $pivotData[$product->id] = [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineTotal,
            ];
        }

        if ($stockErrors !== []) {
            throw ValidationException::withMessages($stockErrors);
        }

        $amounts = $this->calculateAmounts($data, round($subtotal, 2));

        $transaction->products()->attach($pivotData);

        foreach ($pivotData as $productId => $pivot) {
            $products->get($productId)->decrement('stock', $pivot['quantity']);
        }

        $transaction->update($amounts);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, float|string>
     */
    private function calculateAmounts(array $data, float $subtotal): array
    {
        $discountType = $data['discount_type'] ?? 'amount';
        $discountValue = round((float) ($data['discount_value'] ?? 0), 2);
        $taxPercent = round((float) ($data['tax_percent'] ?? 0), 2);
        $method = $data['payment_method'];
        $paid = round((float) ($data['paid_amount'] ?? 0), 2);
        $errors = [];

        $discountAmount = $discountType === 'percent'
            ? round($subtotal * $discountValue / 100, 2)
            : $discountValue;

        if ($discountAmount > $subtotal) {
            $errors['discount_value'] = 'Diskon tidak boleh melebihi subtotal (Rp'.$this->money($subtotal).').';
            $discountAmount = $subtotal;
        }

        $taxable = round($subtotal - $discountAmount, 2);
        $taxAmount = round($taxable * $taxPercent / 100, 2);
        $total = round($taxable + $taxAmount, 2);

        if ($paid < $total && empty($data['customer_id'])) {
            $errors['customer_id'] = 'Pilih pelanggan untuk transaksi yang belum dibayar penuh.';
        }

        if ($method !== 'cash' && $paid > $total) {
            $errors['paid_amount'] = 'Pembayaran '.Transaction::PAYMENT_METHODS[$method].' tidak boleh melebihi total tagihan (Rp'.$this->money($total).').';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return [
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'payment_method' => $method,
            'paid_amount' => $paid,
            'change_amount' => $method === 'cash' ? max(round($paid - $total, 2), 0.0) : 0.0,
            'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ];
    }

    private function restoreStock(Transaction $transaction): void
    {
        foreach ($transaction->products()->get() as $product) {
            $product->increment('stock', $product->pivot->quantity);
        }

        $transaction->products()->detach();
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }

    private function generateCode(): string
    {
        do {
            $code = 'TRX-'.now()->format('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while (Transaction::where('code', $code)->exists());

        return $code;
    }
}
