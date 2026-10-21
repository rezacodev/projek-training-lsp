<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(?Customer $customer, array $items, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customer?->id,
            'transaction_date' => now()->format('Y-m-d'),
            'notes' => 'Pesanan uji',
            'items' => $items,
            'discount_type' => 'amount',
            'discount_value' => 0,
            'tax_percent' => 0,
            'payment_method' => 'cash',
            'paid_amount' => 0,
        ], $overrides);
    }

    public function test_transaction_list_shows_empty_state_and_cashier_link(): void
    {
        $this->get(route('transactions.index'))
            ->assertOk()
            ->assertSeeText('Belum ada transaksi')
            ->assertSeeText('Transaksi Baru (Kasir)')
            ->assertSee('href="'.route('transactions.create').'"', false);
    }

    public function test_cashier_page_lists_searchable_customers_products_and_payment_controls(): void
    {
        Customer::factory()->create(['name' => 'Siti Aminah']);
        Product::factory()->create(['name' => 'Kopi Gayo', 'category' => 'Minuman', 'stock' => 5, 'image' => 'products/kopi.svg']);
        Product::factory()->create(['name' => 'Barang Habis', 'stock' => 0]);

        $this->get(route('transactions.create'))
            ->assertOk()
            ->assertSee('action="'.route('transactions.store').'"', false)
            ->assertSee('id="customer_id"', false)
            ->assertSee('id="product-search"', false)
            ->assertSeeText('Siti Aminah')
            ->assertSeeText('Pelanggan Umum')
            ->assertSeeText('Kopi Gayo')
            ->assertDontSeeText('Barang Habis')
            ->assertSeeText('Minuman')
            ->assertSeeText('Metode Pembayaran')
            ->assertSeeText('Tunai')
            ->assertSeeText('QRIS')
            ->assertSeeText('Kembalian')
            ->assertSeeText('Bayar & Simpan')
            ->assertSee('select2.min.js', false)
            ->assertSee('/uploads/products/kopi.svg', false);
    }

    public function test_cashier_page_is_full_screen_without_admin_chrome(): void
    {
        Product::factory()->create(['stock' => 3]);

        $this->get(route('transactions.create'))
            ->assertOk()
            ->assertSee('class="pos-form"', false)
            ->assertSee('pos-body', false)
            ->assertDontSee('id="accordionSidebar"', false)
            ->assertDontSee('sticky-footer', false)
            ->assertDontSee('topbar', false)
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('href="'.route('transactions.index').'"', false);

        $transaction = app(TransactionService::class)->create($this->payload(null, [
            ['product_id' => Product::first()->id, 'quantity' => 1],
        ], ['paid_amount' => 10000000]));

        $this->get(route('transactions.edit', $transaction))
            ->assertOk()
            ->assertSee('class="pos-form"', false)
            ->assertDontSee('id="accordionSidebar"', false)
            ->assertSeeText('Simpan Perubahan');
    }

    public function test_transaction_can_contain_many_products_and_cash_payment_gives_change(): void
    {
        $customer = Customer::factory()->create();
        $kopi = Product::factory()->create(['price' => 25000, 'stock' => 10]);
        $teh = Product::factory()->create(['price' => 15000.50, 'stock' => 4]);

        $response = $this->post(route('transactions.store'), $this->payload($customer, [
            ['product_id' => $kopi->id, 'quantity' => 3],
            ['product_id' => $teh->id, 'quantity' => 2],
        ], ['paid_amount' => 150000]));

        $transaction = Transaction::firstOrFail();

        $response->assertRedirect(route('transactions.show', $transaction))
            ->assertSessionHas('success', "Transaksi {$transaction->code} berhasil dibuat dan stok produk diperbarui. Kembalian: Rp44.999.");

        $this->assertMatchesRegularExpression('/^TRX-\d{8}-[A-Z0-9]{5}$/', $transaction->code);
        $this->assertSame('105001.00', $transaction->subtotal);
        $this->assertSame('105001.00', $transaction->total);
        $this->assertSame('150000.00', $transaction->paid_amount);
        $this->assertSame('44999.00', $transaction->change_amount);
        $this->assertSame('paid', $transaction->payment_status);
        $this->assertSame('cash', $transaction->payment_method);
        $this->assertCount(2, $transaction->products);
        $this->assertDatabaseHas('product_transaction', [
            'transaction_id' => $transaction->id,
            'product_id' => $kopi->id,
            'quantity' => 3,
            'unit_price' => '25000.00',
            'subtotal' => '75000.00',
        ]);
        $this->assertSame(7, $kopi->fresh()->stock);
        $this->assertSame(2, $teh->fresh()->stock);
    }

    public function test_the_same_product_can_be_sold_in_many_transactions(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $first = Customer::factory()->create();
        $second = Customer::factory()->create();

        $this->post(route('transactions.store'), $this->payload($first, [['product_id' => $product->id, 'quantity' => 2]]));
        $this->post(route('transactions.store'), $this->payload($second, [['product_id' => $product->id, 'quantity' => 3]]));

        $this->assertSame(2, $product->fresh()->transactions()->count());
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_validation_requires_date_items_and_payment_method(): void
    {
        $this->from(route('transactions.create'))
            ->post(route('transactions.store'), ['transaction_date' => '', 'items' => [], 'payment_method' => ''])
            ->assertRedirect(route('transactions.create'))
            ->assertSessionHasErrors([
                'transaction_date' => 'Tanggal transaksi wajib diisi.',
                'items' => 'Tambahkan minimal satu produk pada transaksi.',
                'payment_method' => 'Pilih metode pembayaran.',
            ]);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_validation_rejects_invalid_quantity_duplicate_product_future_date_and_bad_numbers(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->from(route('transactions.create'))
            ->post(route('transactions.store'), $this->payload(null, [
                ['product_id' => $product->id, 'quantity' => 0],
                ['product_id' => $product->id, 'quantity' => 1],
            ], [
                'transaction_date' => now()->addDay()->format('Y-m-d'),
                'discount_type' => 'percent',
                'discount_value' => 150,
                'tax_percent' => -1,
                'paid_amount' => -5,
                'payment_method' => 'barter',
            ]))
            ->assertSessionHasErrors([
                'transaction_date' => 'Tanggal transaksi tidak boleh melewati hari ini.',
                'items.0.quantity' => 'Jumlah pada baris 1 minimal 1.',
                'items.0.product_id' => 'Produk pada baris 1 dipilih lebih dari sekali.',
                'discount_value' => 'Nilai diskon melebihi batas yang diizinkan.',
                'tax_percent' => 'Pajak tidak boleh negatif.',
                'paid_amount' => 'Jumlah bayar tidak boleh negatif.',
                'payment_method' => 'Metode pembayaran tidak valid.',
            ]);

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_transaction_is_rejected_and_rolled_back_when_stock_is_insufficient(): void
    {
        $customer = Customer::factory()->create();
        $ok = Product::factory()->create(['stock' => 5]);
        $low = Product::factory()->create(['name' => 'Barang Langka', 'stock' => 1]);

        $this->from(route('transactions.create'))
            ->post(route('transactions.store'), $this->payload($customer, [
                ['product_id' => $ok->id, 'quantity' => 2],
                ['product_id' => $low->id, 'quantity' => 3],
            ]))
            ->assertRedirect(route('transactions.create'))
            ->assertSessionHasErrors(['items.1.quantity' => 'Stok Barang Langka tidak mencukupi (tersisa 1 unit).']);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('product_transaction', 0);
        $this->assertSame(5, $ok->fresh()->stock);
        $this->assertSame(1, $low->fresh()->stock);
    }

    public function test_percentage_discount_and_tax_are_calculated_on_the_server(): void
    {
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);

        $this->post(route('transactions.store'), $this->payload(null, [
            ['product_id' => $product->id, 'quantity' => 2],
        ], [
            'discount_type' => 'percent',
            'discount_value' => 10,
            'tax_percent' => 11,
            'paid_amount' => 99900,
        ]))->assertSessionHasNoErrors();

        $transaction = Transaction::firstOrFail();

        $this->assertSame('100000.00', $transaction->subtotal);
        $this->assertSame('10000.00', $transaction->discount_amount);
        $this->assertSame('9900.00', $transaction->tax_amount);
        $this->assertSame('99900.00', $transaction->total);
        $this->assertSame('0.00', $transaction->change_amount);
        $this->assertSame('paid', $transaction->payment_status);
    }

    public function test_fixed_discount_cannot_exceed_subtotal(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);

        $this->from(route('transactions.create'))
            ->post(route('transactions.store'), $this->payload(null, [
                ['product_id' => $product->id, 'quantity' => 1],
            ], ['discount_value' => 20000, 'paid_amount' => 10000]))
            ->assertSessionHasErrors(['discount_value' => 'Diskon tidak boleh melebihi subtotal (Rp10.000).']);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_walk_in_customer_can_pay_in_full_without_customer_data(): void
    {
        $product = Product::factory()->create(['price' => 20000, 'stock' => 5]);

        $this->post(route('transactions.store'), $this->payload(null, [
            ['product_id' => $product->id, 'quantity' => 1],
        ], ['paid_amount' => 20000]))->assertSessionHasNoErrors();

        $transaction = Transaction::firstOrFail();

        $this->assertNull($transaction->customer_id);
        $this->assertSame('paid', $transaction->payment_status);

        $this->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSeeText('Pelanggan Umum');
    }

    public function test_underpaid_transaction_requires_a_customer(): void
    {
        $product = Product::factory()->create(['price' => 20000, 'stock' => 5]);

        $this->from(route('transactions.create'))
            ->post(route('transactions.store'), $this->payload(null, [
                ['product_id' => $product->id, 'quantity' => 1],
            ], ['paid_amount' => 5000]))
            ->assertSessionHasErrors(['customer_id' => 'Pilih pelanggan untuk transaksi yang belum dibayar penuh.']);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_partial_and_unpaid_transactions_track_the_outstanding_amount(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['price' => 20000, 'stock' => 10]);

        $this->post(route('transactions.store'), $this->payload($customer, [
            ['product_id' => $product->id, 'quantity' => 2],
        ], ['paid_amount' => 15000]));

        $partial = Transaction::firstOrFail();
        $this->assertSame('partial', $partial->payment_status);
        $this->assertSame(25000.0, $partial->due_amount);

        $this->post(route('transactions.store'), $this->payload($customer, [
            ['product_id' => $product->id, 'quantity' => 1],
        ]))->assertSessionHas('success', fn ($message) => str_contains($message, 'Sisa tagihan: Rp20.000.'));

        $this->assertSame(1, Transaction::where('payment_status', 'unpaid')->count());
    }

    public function test_non_cash_payment_cannot_exceed_the_total(): void
    {
        $product = Product::factory()->create(['price' => 20000, 'stock' => 5]);

        $this->from(route('transactions.create'))
            ->post(route('transactions.store'), $this->payload(null, [
                ['product_id' => $product->id, 'quantity' => 1],
            ], ['payment_method' => 'qris', 'paid_amount' => 25000]))
            ->assertSessionHasErrors(['paid_amount' => 'Pembayaran QRIS tidak boleh melebihi total tagihan (Rp20.000).']);

        $this->post(route('transactions.store'), $this->payload(null, [
            ['product_id' => $product->id, 'quantity' => 1],
        ], ['payment_method' => 'qris', 'paid_amount' => 20000]))->assertSessionHasNoErrors();

        $transaction = Transaction::firstOrFail();
        $this->assertSame('qris', $transaction->payment_method);
        $this->assertSame('0.00', $transaction->change_amount);
    }

    public function test_transaction_receipt_lists_all_purchased_products_and_payment_details(): void
    {
        $customer = Customer::factory()->create(['name' => 'Rina Wati']);
        $a = Product::factory()->create(['name' => 'Produk A', 'price' => 10000, 'stock' => 5]);
        $b = Product::factory()->create(['name' => 'Produk B', 'price' => 20000, 'stock' => 5]);
        $transaction = app(TransactionService::class)->create($this->payload($customer, [
            ['product_id' => $a->id, 'quantity' => 1],
            ['product_id' => $b->id, 'quantity' => 2],
        ], ['paid_amount' => 60000, 'payment_method' => 'cash']));

        $this->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSeeText($transaction->code)
            ->assertSeeText('Struk Penjualan')
            ->assertSeeText('Rina Wati')
            ->assertSeeText('Produk A')
            ->assertSeeText('Produk B')
            ->assertSeeText('Rp50.000')
            ->assertSeeText('Tunai')
            ->assertSeeText('Kembalian')
            ->assertSeeText('Rp10.000')
            ->assertSeeText('Cetak Struk');

        $this->get(route('transactions.index'))
            ->assertOk()
            ->assertSeeText($transaction->code)
            ->assertSeeText('2 produk')
            ->assertSeeText('3 unit')
            ->assertSeeText('Lunas');
    }

    public function test_transaction_list_can_be_searched_and_filtered_by_payment_status(): void
    {
        $budi = Customer::factory()->create(['name' => 'Budi Pencari']);
        $siti = Customer::factory()->create(['name' => 'Siti Lain']);
        $product = Product::factory()->create(['price' => 10000, 'stock' => 20]);
        $service = app(TransactionService::class);
        $paid = $service->create($this->payload($budi, [['product_id' => $product->id, 'quantity' => 1]], ['paid_amount' => 10000]));
        $partial = $service->create($this->payload($siti, [['product_id' => $product->id, 'quantity' => 2]], ['paid_amount' => 5000]));

        $this->get(route('transactions.index', ['q' => 'Pencari']))
            ->assertOk()
            ->assertSeeText($paid->code)
            ->assertDontSeeText($partial->code);

        $this->get(route('transactions.index', ['q' => $partial->code]))
            ->assertSeeText($partial->code)
            ->assertDontSeeText($paid->code);

        $this->get(route('transactions.index', ['status' => 'partial']))
            ->assertOk()
            ->assertSeeText($partial->code)
            ->assertDontSeeText($paid->code)
            ->assertSeeText('Sisa Rp15.000');

        $this->get(route('transactions.index', ['q' => 'tidak-ada-yang-cocok']))
            ->assertSeeText('Tidak ada transaksi yang cocok');
    }

    public function test_transaction_can_be_updated_with_new_items_and_payment(): void
    {
        $customer = Customer::factory()->create();
        $a = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $b = Product::factory()->create(['price' => 5000, 'stock' => 10]);
        $transaction = app(TransactionService::class)->create($this->payload($customer, [
            ['product_id' => $a->id, 'quantity' => 4],
        ], ['paid_amount' => 10000]));
        $this->assertSame('partial', $transaction->fresh()->payment_status);

        $this->get(route('transactions.edit', $transaction))
            ->assertOk()
            ->assertSee('action="'.route('transactions.update', $transaction).'"', false)
            ->assertSeeText('Simpan Perubahan');

        $this->put(route('transactions.update', $transaction), $this->payload($customer, [
            ['product_id' => $a->id, 'quantity' => 6],
            ['product_id' => $b->id, 'quantity' => 2],
        ], ['payment_method' => 'transfer', 'discount_value' => 10000, 'paid_amount' => 60000]))
            ->assertRedirect(route('transactions.show', $transaction))
            ->assertSessionHas('success', fn ($message) => str_starts_with($message, "Transaksi {$transaction->code} berhasil diperbarui dan stok disesuaikan."));

        $transaction->refresh();

        $this->assertSame(4, $a->fresh()->stock);
        $this->assertSame(8, $b->fresh()->stock);
        $this->assertSame('70000.00', $transaction->subtotal);
        $this->assertSame('60000.00', $transaction->total);
        $this->assertSame('transfer', $transaction->payment_method);
        $this->assertSame('paid', $transaction->payment_status);
        $this->assertCount(2, $transaction->products);
    }

    public function test_updating_with_insufficient_stock_keeps_original_transaction_intact(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);
        $transaction = app(TransactionService::class)->create($this->payload($customer, [
            ['product_id' => $product->id, 'quantity' => 3],
        ], ['paid_amount' => 30000]));

        $this->from(route('transactions.edit', $transaction))
            ->put(route('transactions.update', $transaction), $this->payload($customer, [
                ['product_id' => $product->id, 'quantity' => 9],
            ], ['paid_amount' => 90000]))
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame('30000.00', $transaction->fresh()->total);
        $this->assertSame(3, (int) $transaction->fresh()->products->first()->pivot->quantity);
    }

    public function test_transaction_can_be_deleted_and_stock_is_restored(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $transaction = app(TransactionService::class)->create($this->payload($customer, [
            ['product_id' => $product->id, 'quantity' => 4],
        ]));
        $this->assertSame(6, $product->fresh()->stock);

        $this->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHas('success', "Transaksi {$transaction->code} berhasil dihapus dan stok produk dikembalikan.");

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        $this->assertDatabaseCount('product_transaction', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_product_used_in_a_transaction_cannot_be_deleted(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        app(TransactionService::class)->create($this->payload($customer, [
            ['product_id' => $product->id, 'quantity' => 1],
        ]));

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_flash_messages_are_rendered_in_the_layout(): void
    {
        $this->withSession(['success' => 'Berhasil disimpan.', 'error' => 'Terjadi kesalahan.'])
            ->get(route('transactions.index'))
            ->assertSeeText('Berhasil disimpan.')
            ->assertSeeText('Terjadi kesalahan.')
            ->assertSee('alert-success', false)
            ->assertSee('alert-danger', false);
    }

    public function test_sidebar_and_home_link_to_customers_and_transactions(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Total Transaksi')
            ->assertSeeText('Total Pelanggan')
            ->assertSee('href="'.route('transactions.index').'"', false)
            ->assertSee('href="'.route('customers.index').'"', false);
    }
}
