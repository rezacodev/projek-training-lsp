<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Product::IMAGE_DISK);
    }

    public function test_seeder_gives_every_product_an_image_file(): void
    {
        $this->seed();

        $disk = Storage::disk(Product::IMAGE_DISK);

        Product::all()->each(function (Product $product) use ($disk) {
            $this->assertNotNull($product->image, "{$product->sku} tidak memiliki gambar.");
            $disk->assertExists($product->image);
            $this->assertStringContainsString('<svg', $disk->get($product->image));
        });
    }

    public function test_seeder_creates_realistic_master_data(): void
    {
        $this->seed();

        $this->assertGreaterThanOrEqual(20, Product::count());
        $this->assertLessThanOrEqual(40, Product::count());
        $this->assertGreaterThanOrEqual(8, Product::distinct()->count('category'));
        $this->assertGreaterThanOrEqual(10, Customer::count());
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertSame(0, Product::where('price', '<=', 0)->count());
        $this->assertSame(0, Product::where('stock', '<', 0)->count());
    }

    public function test_seeder_creates_varied_and_consistent_transactions(): void
    {
        $this->seed();

        $this->assertGreaterThanOrEqual(20, Transaction::count());

        foreach (['paid', 'partial', 'unpaid'] as $status) {
            $this->assertGreaterThan(0, Transaction::where('payment_status', $status)->count(), "Tidak ada transaksi berstatus {$status}.");
        }

        $this->assertGreaterThanOrEqual(3, Transaction::distinct()->count('payment_method'));
        $this->assertGreaterThan(0, Transaction::whereNull('customer_id')->count());
        $this->assertGreaterThan(0, Transaction::where('discount_amount', '>', 0)->count());
        $this->assertGreaterThan(0, Transaction::where('tax_amount', '>', 0)->count());
        $this->assertGreaterThan(0, Transaction::withCount('products')->get()->where('products_count', '>', 1)->count());
        $this->assertSame(0, Transaction::whereNull('customer_id')->where('payment_status', '!=', 'paid')->count());

        Transaction::with('products')->get()->each(function (Transaction $transaction) {
            $subtotal = (float) $transaction->products->sum(fn ($product) => (float) $product->pivot->subtotal);

            $this->assertEqualsWithDelta($subtotal, (float) $transaction->subtotal, 0.01);
            $this->assertEqualsWithDelta(
                (float) $transaction->subtotal - (float) $transaction->discount_amount + (float) $transaction->tax_amount,
                (float) $transaction->total,
                0.01,
            );
            $this->assertTrue($transaction->transaction_date->between(today()->subDays(29), today()));
        });
    }

    public function test_seeded_stock_matches_the_quantities_sold(): void
    {
        $this->seed();

        $initial = [];
        $number = 1;

        foreach ((new ReflectionMethod(ProductSeeder::class, 'catalog'))->invoke(new ProductSeeder) as $items) {
            foreach ($items as [, , $stock]) {
                $initial[sprintf('SKU-%03d', $number++)] = $stock;
            }
        }

        $sold = DB::table('product_transaction')
            ->select('product_id', DB::raw('SUM(quantity) as quantity'))
            ->groupBy('product_id')
            ->pluck('quantity', 'product_id');

        $this->assertGreaterThan(0, $sold->count());

        Product::all()->each(function (Product $product) use ($initial, $sold) {
            $this->assertSame(
                $initial[$product->sku] - (int) $sold->get($product->id, 0),
                $product->stock,
                "Stok {$product->sku} tidak sesuai dengan jumlah terjual.",
            );
        });
    }

    public function test_seeder_can_be_run_repeatedly_without_duplicates_or_changing_stock(): void
    {
        $this->seed();

        $counts = [Product::count(), Customer::count(), User::count(), Transaction::count()];
        $stock = Product::orderBy('id')->pluck('stock', 'id')->all();

        $this->seed();

        $this->assertSame($counts, [Product::count(), Customer::count(), User::count(), Transaction::count()]);
        $this->assertSame($stock, Product::orderBy('id')->pluck('stock', 'id')->all());
    }
}
