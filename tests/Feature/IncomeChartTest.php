<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeChartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_it_shows_daily_incomes_for_the_selected_month(): void
    {
        $this->user->incomes()->create([
            'title' => 'Uang awal',
            'amount' => 10000,
            'date' => '2026-11-02',
            'type' => 'opening_balance',
        ]);
        $this->user->incomes()->create([
            'title' => 'Uang saku',
            'amount' => 5000,
            'date' => '2026-11-02',
            'type' => 'income',
        ]);
        $this->user->incomes()->create([
            'title' => 'Bonus',
            'amount' => 3000,
            'date' => '2026-11-06',
            'type' => 'income',
        ]);
        $this->user->incomes()->create([
            'title' => 'Pendapatan Oktober',
            'amount' => 25000,
            'date' => '2026-10-09',
            'type' => 'income',
        ]);

        $this->get(route('incomes.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('Pemasukan bulan November 2026')
            ->assertSee(route('home'))
            ->assertSee('Kembali ke Beranda')
            ->assertSee('Rp 43.000')
            ->assertSee('02 Nov: Rp 15.000', false)
            ->assertSee('06 Nov: Rp 3.000', false)
            ->assertDontSee('09 Oct: Rp 25.000', false);
    }
}
