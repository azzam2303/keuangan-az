<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_seeder_creates_the_requested_admin_login(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->post(route('login.store'), [
            'username' => 'admin20',
            'password' => 'admin123',
        ])->assertRedirect(route('admin.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'admin20',
            'is_admin' => true,
        ]);
        $this->get(route('home'))->assertRedirect(route('admin.index'));
        $this->get(route('incomes.create'))->assertRedirect(route('admin.index'));
        $this->get(route('expenses.create'))->assertRedirect(route('admin.index'));

        $this->actingAs(User::where('username', 'admin20')->firstOrFail())
            ->get(route('admin.index'))
            ->assertOk()
            ->assertDontSee(route('incomes.create'))
            ->assertDontSee(route('expenses.create'));
    }

    public function test_only_admins_can_open_the_admin_page_or_run_admin_actions(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['username' => 'regularuser']);

        $this->get(route('admin.index'))->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.users.reset', $user))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.users.destroy', $user))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Kelola akun')
            ->assertSee('regularuser')
            ->assertSee('href="'.route('admin.users.create').'"', false)
            ->assertSee('Daftar akun');

        $this->actingAs($user)
            ->get(route('admin.users.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.users.store'), [
                'username' => 'created-by-user',
                'password' => 'a-strong-password',
                'password_confirmation' => 'a-strong-password',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('incomes.store'), [])
            ->assertRedirect(route('admin.index'));

        $this->actingAs($admin)
            ->post(route('expenses.store'), [])
            ->assertRedirect(route('admin.index'));
    }

    public function test_admin_can_create_a_regular_account(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Daftar akun baru');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'username' => 'NewPerson',
                'password' => 'a-strong-password',
                'password_confirmation' => 'a-strong-password',
            ])
            ->assertRedirect(route('admin.index'))
            ->assertSessionHas('success', 'Akun newperson berhasil dibuat.');

        $this->assertDatabaseHas('users', [
            'username' => 'newperson',
            'name' => 'newperson',
            'is_admin' => false,
        ]);
    }

    public function test_admin_can_reset_only_the_selected_users_finances(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['username' => 'reset-me']);
        $otherUser = User::factory()->create(['username' => 'keep-me']);

        $user->expenses()->create(['title' => 'Lunch', 'amount' => 100, 'date' => '2026-11-01']);
        $user->incomes()->create(['title' => 'Salary', 'amount' => 500, 'date' => '2026-11-01']);
        $otherUser->expenses()->create(['title' => 'Books', 'amount' => 50, 'date' => '2026-11-01']);
        $otherUser->incomes()->create(['title' => 'Gift', 'amount' => 80, 'date' => '2026-11-01']);

        $this->actingAs($admin)
            ->post(route('admin.users.reset', $user))
            ->assertRedirect(route('admin.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('expenses', 1);
        $this->assertDatabaseCount('incomes', 1);
        $this->assertDatabaseHas('expenses', ['user_id' => $otherUser->id, 'title' => 'Books']);
        $this->assertDatabaseHas('incomes', ['user_id' => $otherUser->id, 'title' => 'Gift']);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_admin_reset_and_delete_support_ajax_responses(): void
    {
        $admin = $this->createAdmin();
        $resetUser = User::factory()->create(['username' => 'ajax-reset']);
        $deleteUser = User::factory()->create(['username' => 'ajax-delete']);

        $resetUser->expenses()->create(['title' => 'Lunch', 'amount' => 100, 'date' => '2026-11-01']);
        $resetUser->incomes()->create(['title' => 'Salary', 'amount' => 500, 'date' => '2026-11-01']);

        $this->actingAs($admin)
            ->postJson(route('admin.users.reset', $resetUser))
            ->assertOk()
            ->assertJson([
                'message' => 'Data keuangan akun ajax-reset berhasil direset.',
                'expensesCount' => 0,
                'incomesCount' => 0,
            ]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.users.destroy', $deleteUser))
            ->assertOk()
            ->assertJson([
                'message' => 'Akun ajax-delete berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('users', ['id' => $deleteUser->id]);
        $this->assertDatabaseHas('users', ['id' => $resetUser->id]);
        $this->assertDatabaseMissing('expenses', ['user_id' => $resetUser->id]);
        $this->assertDatabaseMissing('incomes', ['user_id' => $resetUser->id]);
    }

    public function test_admin_can_delete_a_regular_account_but_cannot_manage_admin_accounts(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['username' => 'delete-me']);
        $user->expenses()->create(['title' => 'Lunch', 'amount' => 100, 'date' => '2026-11-01']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('expenses', ['user_id' => $user->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.users.reset', $admin))
            ->assertNotFound();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_registration_cannot_claim_the_admin_username(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'username' => 'Admin20',
                'password' => 'a-strong-password',
                'password_confirmation' => 'a-strong-password',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('username');

        $this->assertDatabaseMissing('users', ['username' => 'admin20']);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create(['username' => 'admin20']);
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
