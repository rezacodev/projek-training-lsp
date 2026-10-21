<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Product::IMAGE_DISK);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'sku' => 'IMG-001',
            'name' => 'Produk Bergambar',
            'category' => 'Uji',
            'price' => '10000',
            'stock' => '5',
        ], $overrides);
    }

    public function test_product_can_be_created_with_an_image(): void
    {
        $this->post(route('products.store'), $this->payload([
            'image' => UploadedFile::fake()->image('foto.jpg', 400, 300),
        ]))->assertRedirect(route('products.index'));

        $product = Product::firstOrFail();

        $this->assertNotNull($product->image);
        $this->assertStringStartsWith('products/', $product->image);
        Storage::disk(Product::IMAGE_DISK)->assertExists($product->image);
        $this->assertStringEndsWith('/uploads/'.$product->image, $product->image_url);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee($product->image_url, false);
    }

    public function test_product_without_image_has_no_image_url(): void
    {
        $this->post(route('products.store'), $this->payload())->assertRedirect(route('products.index'));

        $this->assertNull(Product::firstOrFail()->image_url);
    }

    public function test_image_must_be_a_supported_picture_within_the_size_limit(): void
    {
        $this->from(route('products.create'))
            ->post(route('products.store'), $this->payload(['image' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')]))
            ->assertSessionHasErrors(['image' => 'File gambar produk harus berupa gambar.']);

        $this->from(route('products.create'))
            ->post(route('products.store'), $this->payload(['image' => UploadedFile::fake()->image('besar.jpg')->size(3000)]))
            ->assertSessionHasErrors(['image' => 'Ukuran gambar produk maksimal 2 MB.']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_replacing_an_image_removes_the_old_file(): void
    {
        $this->post(route('products.store'), $this->payload(['image' => UploadedFile::fake()->image('lama.jpg')]));
        $product = Product::firstOrFail();
        $oldPath = $product->image;

        $this->put(route('products.update', $product), $this->payload([
            'image' => UploadedFile::fake()->image('baru.png'),
        ]))->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertNotSame($oldPath, $product->image);
        Storage::disk(Product::IMAGE_DISK)->assertMissing($oldPath);
        Storage::disk(Product::IMAGE_DISK)->assertExists($product->image);
    }

    public function test_updating_without_a_new_file_keeps_the_existing_image(): void
    {
        $this->post(route('products.store'), $this->payload(['image' => UploadedFile::fake()->image('tetap.jpg')]));
        $product = Product::firstOrFail();
        $path = $product->image;

        $this->put(route('products.update', $product), $this->payload(['name' => 'Nama Baru']))
            ->assertRedirect(route('products.index'));

        $this->assertSame($path, $product->fresh()->image);
        Storage::disk(Product::IMAGE_DISK)->assertExists($path);
    }

    public function test_image_can_be_removed_from_a_product(): void
    {
        $this->post(route('products.store'), $this->payload(['image' => UploadedFile::fake()->image('hapus.jpg')]));
        $product = Product::firstOrFail();
        $path = $product->image;

        $this->put(route('products.update', $product), $this->payload(['remove_image' => '1']))
            ->assertRedirect(route('products.index'));

        $this->assertNull($product->fresh()->image);
        Storage::disk(Product::IMAGE_DISK)->assertMissing($path);
    }

    public function test_deleting_a_product_removes_its_image_file(): void
    {
        $this->post(route('products.store'), $this->payload(['image' => UploadedFile::fake()->image('akhir.jpg')]));
        $product = Product::firstOrFail();
        $path = $product->image;

        $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk(Product::IMAGE_DISK)->assertMissing($path);
    }

    public function test_edit_form_accepts_file_uploads_and_previews_the_current_image(): void
    {
        $this->post(route('products.store'), $this->payload(['image' => UploadedFile::fake()->image('pratinjau.jpg')]));
        $product = Product::firstOrFail();

        $this->get(route('products.create'))->assertOk()->assertSee('enctype="multipart/form-data"', false);
        $this->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee($product->image_url, false)
            ->assertSeeText('Hapus gambar saat ini');
    }
}
