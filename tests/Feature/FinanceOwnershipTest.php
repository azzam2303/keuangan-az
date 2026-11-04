<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_user_only_sees_their_own_finances_and_starts_with_zero(): void
    {
        $userA = User::factory()->create(['username' => 'usera']);
        $userB = User::factory()->create(['username' => 'userb']);

        $expenseA = $userA->expenses()->create([
            'title' => 'Belanja User A',
            'amount' => 7000000,
            'date' => now()->toDateString(),
            'description' => 'Catatan User A',
        ]);
        $userA->incomes()->create([
            'title' => 'Saldo User A',
            'amount' => 10000000,
            'date' => now()->toDateString(),
            'description' => 'Pemasukan User A',
            'type' => 'opening_balance',
        ]);

        $this->actingAs($userB)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Rp 0')
            ->assertDontSee('Rp 10.000.000')
            ->assertDontSee('Rp 7.000.000');

        $this->get(route('expenses.index'))
            ->assertOk()
            ->assertDontSee('Belanja User A')
            ->assertDontSee('Catatan User A');

        $this->get(route('incomes.index'))
            ->assertOk()
            ->assertDontSee('Saldo User A')
            ->assertDontSee('Pemasukan User A');
    }

    public function test_users_cannot_edit_or_delete_another_users_transactions(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $otherUser = User::factory()->create(['username' => 'other']);
        $expense = $owner->expenses()->create([
            'title' => 'Private expense',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Owner record',
        ]);
        $income = $owner->incomes()->create([
            'title' => 'Private income',
            'amount' => 2000,
            'date' => now()->toDateString(),
            'description' => 'Owner record',
            'type' => 'income',
        ]);

        $this->actingAs($otherUser)
            ->get(route('expenses.edit', $expense))
            ->assertNotFound();

        $this->put(route('expenses.update', $expense), [
            'title' => 'Changed expense',
            'amount' => 1,
            'date' => now()->toDateString(),
            'description' => 'Changed record',
        ])->assertNotFound();

        $this->delete(route('expenses.destroy', $expense))->assertNotFound();
        $this->get(route('incomes.edit', $income))->assertNotFound();
        $this->delete(route('incomes.destroy', $income))->assertNotFound();

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'title' => 'Private expense']);
        $this->assertDatabaseHas('incomes', ['id' => $income->id, 'title' => 'Private income']);
    }

    public function test_wallet_button_opens_a_separate_read_only_page_with_only_own_transactions(): void
    {
        $user = User::factory()->create(['username' => 'wallet-owner']);
        $otherUser = User::factory()->create(['username' => 'wallet-other']);
        $user->incomes()->create([
            'title' => 'Gaji November',
            'amount' => 5000000,
            'date' => '2026-11-01',
            'description' => 'Gaji dari kantor',
            'type' => 'income',
        ]);
        $user->expenses()->create([
            'title' => 'Belanja bulanan',
            'amount' => 750000,
            'date' => '2026-11-02',
            'description' => 'Belanja kebutuhan rumah',
        ]);
        $otherUser->incomes()->create([
            'title' => 'Rahasia user lain',
            'amount' => 9000000,
            'date' => '2026-11-03',
            'description' => 'Jangan ditampilkan',
            'type' => 'income',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee(route('wallet.transactions'))
            ->assertDontSee('Gaji November');

        $this->get(route('wallet.transactions'))
            ->assertOk()
            ->assertSee('riwayat keuangan')
            ->assertSee('Semua transaksi')
            ->assertDontSee('rincian dompet')
            ->assertDontSee('Sisa dana saat ini')
            ->assertSee('Kembali ke Beranda')
            ->assertSee(route('home'))
            ->assertSee('Gaji November')
            ->assertSee('Gaji dari kantor')
            ->assertSee('01/11/2026')
            ->assertSee('Belanja bulanan')
            ->assertSee('Belanja kebutuhan rumah')
            ->assertSee('02/11/2026')
            ->assertDontSee('Rahasia user lain')
            ->assertDontSee('Jangan ditampilkan')
            ->assertDontSee('Edit')
            ->assertDontSee('Hapus');
    }

    public function test_deleting_expense_with_json_returns_updated_totals_and_chart_data(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 15));
        $user = User::factory()->create(['username' => 'expense-owner']);
        $otherUser = User::factory()->create(['username' => 'expense-other']);
        $expense = $user->expenses()->create([
            'title' => 'Expense to delete',
            'amount' => 1000,
            'date' => '2026-11-02',
        ]);
        $user->expenses()->create([
            'title' => 'Remaining expense',
            'amount' => 2500,
            'date' => '2026-11-03',
        ]);
        $otherUser->expenses()->create([
            'title' => 'Other user expense',
            'amount' => 9000,
            'date' => '2026-11-02',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('expenses.destroy', $expense), ['month' => '2026-11'])
            ->assertOk()
            ->assertJsonPath('total', 2500)
            ->assertJsonPath('monthlyTotal', 2500)
            ->assertJsonPath('daysWithTransactions', 1)
            ->assertJsonPath('dailyTotals.2026-11-03', 2500)
            ->assertJsonMissingPath('dailyTotals.2026-11-02');

        $this->assertIsNumeric($response->json('total'));
        $this->assertIsNumeric($response->json('monthlyTotal'));
        $this->assertIsNumeric($response->json('dailyTotals.2026-11-03'));
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
        $this->assertDatabaseHas('expenses', ['user_id' => $otherUser->id, 'title' => 'Other user expense']);
    }

    public function test_invalid_json_delete_month_does_not_delete_income(): void
    {
        $income = User::factory()->create()->incomes()->create([
            'title' => 'Income record',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'type' => 'income',
        ]);

        $this->actingAs($income->user)
            ->deleteJson(route('incomes.destroy', $income), ['month' => 'invalid'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('incomes', ['id' => $income->id]);
    }

    public function test_deleting_income_with_json_returns_updated_totals_and_chart_data(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 15));
        $user = User::factory()->create(['username' => 'income-owner']);
        $income = $user->incomes()->create([
            'title' => 'Income to delete',
            'amount' => 1000,
            'date' => '2026-11-02',
            'type' => 'income',
        ]);
        $user->incomes()->create([
            'title' => 'Remaining income',
            'amount' => 2500,
            'date' => '2026-11-03',
            'type' => 'opening_balance',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('incomes.destroy', $income), ['month' => '2026-11'])
            ->assertOk()
            ->assertJsonPath('total', 2500)
            ->assertJsonPath('monthlyTotal', 2500)
            ->assertJsonPath('daysWithTransactions', 1)
            ->assertJsonPath('dailyTotals.2026-11-03', 2500)
            ->assertJsonMissingPath('dailyTotals.2026-11-02');

        $this->assertIsNumeric($response->json('total'));
        $this->assertIsNumeric($response->json('monthlyTotal'));
        $this->assertIsNumeric($response->json('dailyTotals.2026-11-03'));
        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }
}
