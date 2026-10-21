<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_list_and_create_form_are_available(): void
    {
        $user = User::factory()->create();

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSeeText($user->name)
            ->assertSeeText($user->email)
            ->assertSeeText('Kelola data nama dan email pengguna aplikasi dari menu ini.');

        $this->get(route('users.create'))
            ->assertOk()
            ->assertSee('action="'.route('users.store').'"', false);
    }

    public function test_user_list_paginates_records(): void
    {
        User::factory()->count(11)->create();

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('pagination')
            ->assertSee('Next');
    }

    public function test_user_can_be_created_with_a_hashed_password(): void
    {
        $response = $this->post(route('users.store'), [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.test',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.test',
        ]);
        $this->assertTrue(password_verify('rahasia123', User::where('email', 'baru@example.test')->firstOrFail()->password));
    }

    public function test_user_form_rejects_duplicate_email_addresses(): void
    {
        User::factory()->create(['email' => 'sudah-ada@example.test']);

        $this->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Pengguna Baru',
                'email' => 'sudah-ada@example.test',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_be_updated_without_changing_the_password(): void
    {
        $user = User::factory()->create(['password' => 'kata-sandi-lama']);
        $password = $user->password;

        $this->get(route('users.edit', $user))
            ->assertOk()
            ->assertSee('action="'.route('users.update', $user).'"', false);

        $this->put(route('users.update', $user), [
            'name' => 'Nama Diperbarui',
            'email' => $user->email,
            'password' => '',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Diperbarui',
            'email' => $user->email,
        ]);
        $this->assertSame($password, $user->fresh()->password);
    }

    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_home_page_displays_user_management(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeText('Selamat Datang')
            ->assertSee('href="'.route('users.index').'"', false)
            ->assertSee('href="'.route('users.create').'"', false);
    }

    public function test_shared_navigation_links_to_home_and_user_pages(): void
    {
        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('href="'.route('users.index').'"', false)
            ->assertSee('href="'.route('users.create').'"', false)
            ->assertSee('Tambah Pengguna')
            ->assertDontSee('collapseUsers');
    }
}
