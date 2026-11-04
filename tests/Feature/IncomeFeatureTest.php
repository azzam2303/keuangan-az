<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_it_records_opening_balance_and_income_and_calculates_the_home_balance(): void
    {
        $this->post(route('incomes.store'), [
            'title' => 'Uang awal',
            'amount' => 50000,
            'date' => now()->toDateString(),
            'description' => 'Saldo awal bulan',
            'type' => 'opening_balance',
        ])->assertRedirect(route('incomes.index'));

        $this->post(route('incomes.store'), [
            'title' => 'Uang saku',
            'amount' => 25000,
            'date' => now()->toDateString(),
            'description' => 'Uang saku bulanan',
            'type' => 'income',
        ])->assertRedirect(route('incomes.index'));

        $this->user->expenses()->create([
            'title' => 'Makan siang',
            'amount' => 15000,
            'date' => '2026-11-02',
        ]);

        $this->assertDatabaseCount('incomes', 2);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Rp 60.000')
            ->assertSee('Rp 75.000')
            ->assertSee(route('incomes.index'))
            ->assertSee(route('expenses.index'))
            ->assertDontSee('Navigasi utama');
    }

    public function test_income_type_must_be_a_supported_value(): void
    {
        $this->from(route('incomes.create'))
            ->post(route('incomes.store'), [
                'title' => 'Invalid type',
                'amount' => 10000,
                'date' => now()->toDateString(),
                'description' => 'Pemasukan valid',
                'type' => 'expense',
            ])
            ->assertRedirect(route('incomes.create'))
            ->assertSessionHasErrors('type');
    }

    public function test_income_create_and_edit_forms_are_available(): void
    {
        $income = $this->user->incomes()->create([
            'title' => 'Uang awal',
            'amount' => 50000,
            'date' => '2026-11-01',
            'type' => 'opening_balance',
        ]);

        $this->get(route('incomes.create'))
            ->assertOk()
            ->assertSee('Tambah pemasukan')
            ->assertSee('Uang awal')
            ->assertSee('Pendapatan')
            ->assertSee('name="description"', false)
            ->assertSee('min="'.now()->toDateString().'"', false)
            ->assertSee('required', false);

        $this->get(route('incomes.edit', $income))
            ->assertOk()
            ->assertSee('Edit pemasukan')
            ->assertSee('Uang awal');
    }

    public function test_income_requires_alphanumeric_title_and_description_when_creating_and_editing(): void
    {
        $invalidData = [
            'title' => 'Uang saku!',
            'amount' => 25000,
            'date' => '2026-11-02',
            'description' => '',
            'type' => 'income',
        ];

        $this->from(route('incomes.create'))
            ->post(route('incomes.store'), $invalidData)
            ->assertRedirect(route('incomes.create'))
            ->assertSessionHasErrors(['title', 'description']);

        $income = $this->user->incomes()->create([
            'title' => 'Uang saku',
            'amount' => 25000,
            'date' => '2026-11-02',
            'description' => 'Uang saku bulanan',
            'type' => 'income',
        ]);

        $this->from(route('incomes.edit', $income))
            ->put(route('incomes.update', $income), $invalidData)
            ->assertRedirect(route('incomes.edit', $income))
            ->assertSessionHasErrors(['title', 'description']);
    }

    public function test_income_requires_all_fields(): void
    {
        $this->from(route('incomes.create'))
            ->post(route('incomes.store'), [])
            ->assertRedirect(route('incomes.create'))
            ->assertSessionHasErrors(['title', 'amount', 'date', 'description', 'type']);
    }

    public function test_income_rejects_dates_before_today_when_creating_and_editing(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 3)->setTime(12, 0));
        $validData = [
            'title' => 'Uang saku',
            'amount' => 25000,
            'date' => '2026-11-02',
            'description' => 'Uang saku bulanan',
            'type' => 'income',
        ];

        $this->from(route('incomes.create'))
            ->post(route('incomes.store'), $validData)
            ->assertRedirect(route('incomes.create'))
            ->assertSessionHasErrors('date');

        $income = $this->user->incomes()->create([
            ...$validData,
            'date' => '2026-11-03',
        ]);

        $this->from(route('incomes.edit', $income))
            ->put(route('incomes.update', $income), $validData)
            ->assertRedirect(route('incomes.edit', $income))
            ->assertSessionHasErrors('date');

        $this->travelBack();
    }
}
