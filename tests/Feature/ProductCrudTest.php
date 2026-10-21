<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_list_and_create_form_are_available_when_catalog_is_empty(): void
    {
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSeeText('Inventaris Produk')
            ->assertSeeText('Inventaris masih kosong')
            ->assertSee('href="'.route('products.create').'"', false);

        $this->get(route('products.create'))
            ->assertOk()
            ->assertSee('action="'.route('products.store').'"', false)
            ->assertSee('href="'.route('products.index').'"', false);
    }

    public function test_product_list_displays_inventory_data_and_actions(): void
    {
        $product = Product::factory()->create([
            'sku' => 'PRD-CAFE',
            'name' => 'Kopi Nusantara',
            'category' => 'Minuman',
            'price' => 42500,
            'stock' => 12,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSeeText('Kopi Nusantara')
            ->assertSeeText('PRD-CAFE')
            ->assertSeeText('Minuman')
            ->assertSeeText('12 unit')
            ->assertSee('href="'.route('products.edit', $product).'"', false)
            ->assertSee('action="'.route('products.destroy', $product).'"', false);
    }

    public function test_product_can_be_created(): void
    {
        $this->post(route('products.store'), [
            'sku' => 'PRD-BARU',
            'name' => 'Tas Harian',
            'category' => 'Aksesori',
            'price' => '149000',
            'stock' => '8',
            'description' => 'Tas harian serbaguna.',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'PRD-BARU',
            'name' => 'Tas Harian',
            'category' => 'Aksesori',
            'price' => '149000.00',
            'stock' => 8,
        ]);
    }

    public function test_product_form_rejects_duplicate_sku(): void
    {
        Product::factory()->create(['sku' => 'PRD-SAMA']);

        $this->from(route('products.create'))
            ->post(route('products.store'), [
                'sku' => 'PRD-SAMA',
                'name' => 'Produk Duplikat',
                'category' => 'Umum',
                'price' => '10000',
                'stock' => '1',
            ])
            ->assertSessionHasErrors('sku');
    }

    public function test_product_can_be_updated_without_changing_its_sku(): void
    {
        $product = Product::factory()->create([
            'sku' => 'PRD-LAMA',
            'name' => 'Produk Lama',
        ]);

        $this->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('action="'.route('products.update', $product).'"', false);

        $this->put(route('products.update', $product), [
            'sku' => 'PRD-LAMA',
            'name' => 'Produk Diperbarui',
            'category' => $product->category,
            'price' => '25000',
            'stock' => '4',
            'description' => '',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'PRD-LAMA',
            'name' => 'Produk Diperbarui',
            'price' => '25000.00',
            'stock' => 4,
        ]);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_validation_rejects_negative_price_and_stock(): void
    {
        $this->from(route('products.create'))
            ->post(route('products.store'), [
                'sku' => 'PRD-NEGATIF',
                'name' => 'Produk Invalid',
                'category' => 'Umum',
                'price' => '-1',
                'stock' => '-1',
            ])
            ->assertSessionHasErrors(['price', 'stock']);
    }

    public function test_home_page_links_to_product_inventory(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Inventaris Produk')
            ->assertSeeText('Total Produk')
            ->assertSee('href="'.route('products.index').'"', false)
            ->assertSee('href="'.route('products.create').'"', false);
    }

    public function test_product_navigation_is_available_in_the_sidebar(): void
    {
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('href="'.route('products.index').'"', false)
            ->assertSeeText('Produk');
    }
}
