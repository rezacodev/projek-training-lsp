<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_list_shows_empty_state_and_create_action(): void
    {
        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSeeText('Belum ada pelanggan')
            ->assertSee('href="'.route('customers.create').'"', false);

        $this->get(route('customers.create'))
            ->assertOk()
            ->assertSee('action="'.route('customers.store').'"', false);
    }

    public function test_customer_can_be_created_with_flash_message(): void
    {
        $this->post(route('customers.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'phone' => '0812-3456-7890',
            'address' => 'Jakarta',
        ])
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success', 'Pelanggan Budi Santoso berhasil ditambahkan.');

        $this->assertDatabaseHas('customers', ['email' => 'budi@example.test']);
    }

    public function test_customer_validation_returns_indonesian_messages(): void
    {
        Customer::factory()->create(['email' => 'sama@example.test']);

        $this->from(route('customers.create'))
            ->post(route('customers.store'), [
                'name' => '',
                'email' => 'sama@example.test',
                'phone' => 'abc',
            ])
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors([
                'name' => 'Nama pelanggan wajib diisi.',
                'email' => 'Email ini sudah terdaftar pada pelanggan lain.',
                'phone' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
            ]);
    }

    public function test_customer_can_be_updated_keeping_the_same_email(): void
    {
        $customer = Customer::factory()->create(['email' => 'tetap@example.test']);

        $this->get(route('customers.edit', $customer))
            ->assertOk()
            ->assertSee('action="'.route('customers.update', $customer).'"', false);

        $this->put(route('customers.update', $customer), [
            'name' => 'Nama Baru',
            'email' => 'tetap@example.test',
            'phone' => '08123456789',
            'address' => '',
        ])
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Nama Baru']);
    }

    public function test_customer_without_transactions_can_be_deleted(): void
    {
        $customer = Customer::factory()->create();

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_customer_with_transactions_cannot_be_deleted(): void
    {
        $customer = Customer::factory()->create();
        Transaction::factory()->for($customer)->create();

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
