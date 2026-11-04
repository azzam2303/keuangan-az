<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page_and_protected_pages_redirect_to_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('SELAMAT DATANG KEMBALI')
            ->assertSee('name="username"', false)
            ->assertDontSee('name="email"', false)
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('Buat akun baru')
            ->assertSee('data-password-toggle', false)
            ->assertSee('type="password"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('BUAT AKUN BARU')
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('data-password-toggle', false);

        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('home'))->assertRedirect(route('login'));
        $this->get(route('incomes.index'))->assertRedirect(route('login'));
        $this->get(route('expenses.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'username' => 'student',
            'password' => 'correct-password',
        ]);

        $this->post(route('login.store'), [
            'username' => 'student',
            'password' => 'correct-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('home'))->assertOk();

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_invalid_credentials_return_a_login_error(): void
    {
        User::factory()->create([
            'username' => 'student',
            'password' => 'correct-password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'username' => 'student',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->get(route('login'))->assertSee('Login belum berhasil');

        $this->assertGuest();
    }

    public function test_unregistered_username_shows_a_registration_prompt(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'username' => 'unknownstudent',
                'password' => 'a-strong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'username' => 'Akun tidak terdaftar. Silakan buat akun baru terlebih dahulu.',
            ]);

        $this->get(route('login'))
            ->assertSee('Akun tidak terdaftar. Silakan buat akun baru terlebih dahulu.')
            ->assertSee('Buat akun baru');

        $this->assertGuest();
    }

    public function test_user_can_register_and_must_login_after_account_creation(): void
    {
        $this->post(route('register.store'), [
            'username' => 'NewStudent',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Akun berhasil dibuat. Silakan login dengan username dan kata sandi yang baru dibuat.');

        $this->assertDatabaseHas('users', [
            'username' => 'newstudent',
            'name' => 'newstudent',
            'email' => null,
        ]);
        $this->assertGuest();
        $this->get(route('login'))->assertSee('Akun berhasil dibuat');

        $this->post(route('login.store'), [
            'username' => 'NewStudent',
            'password' => 'a-strong-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }

    public function test_registration_validates_username_uniqueness_and_password_confirmation(): void
    {
        User::factory()->create(['username' => 'existing']);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'username' => 'existing',
                'password' => 'a-strong-password',
                'password_confirmation' => 'different-password',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['username', 'password']);

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }
}
