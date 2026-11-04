<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseChartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_it_shows_daily_expenses_for_the_selected_month(): void
    {
        $this->user->expenses()->create([
            'title' => 'Lunch',
            'amount' => 10000,
            'date' => '2026-11-02',
        ]);
        $this->user->expenses()->create([
            'title' => 'Coffee',
            'amount' => 5000,
            'date' => '2026-11-02',
        ]);
        $this->user->expenses()->create([
            'title' => 'Groceries',
            'amount' => 3000,
            'date' => '2026-11-06',
        ]);
        $this->user->expenses()->create([
            'title' => 'October expense',
            'amount' => 25000,
            'date' => '2026-10-09',
        ]);

        $this->get(route('expenses.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('Pengeluaran bulan November 2026')
            ->assertSee(route('home'))
            ->assertSee('Kembali ke Beranda')
            ->assertSee('Rp 18.000')
            ->assertSee('02 Nov: Rp 15.000', false)
            ->assertSee('06 Nov: Rp 3.000', false)
            ->assertDontSee('09 Oct: Rp 25.000', false);
    }

    public function test_month_arrows_navigate_between_months_without_going_into_the_future(): void
    {
        $this->travelTo(Carbon::parse('2026-11-15 12:00:00'));

        $this->get(route('expenses.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee(route('expenses.index', ['month' => '2026-10']))
            ->assertSee('Sudah di bulan terbaru');

        $this->get(route('expenses.index', ['month' => '2026-10']))
            ->assertOk()
            ->assertSee(route('expenses.index', ['month' => '2026-09']))
            ->assertSee(route('expenses.index', ['month' => '2026-11']));

        $this->travelBack();
    }
}
