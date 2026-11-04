<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_expense_requires_alphanumeric_title_and_description_when_creating_and_editing(): void
    {
        $invalidData = [
            'title' => 'Makan siang!',
            'amount' => 15000,
            'date' => '2026-11-02',
            'description' => '',
        ];

        $this->from(route('expenses.create'))
            ->post(route('expenses.store'), $invalidData)
            ->assertRedirect(route('expenses.create'))
            ->assertSessionHasErrors(['title', 'description']);

        $expense = $this->user->expenses()->create([
            'title' => 'Makan siang',
            'amount' => 15000,
            'date' => '2026-11-02',
            'description' => 'Makan di kantin',
        ]);

        $this->from(route('expenses.edit', $expense))
            ->put(route('expenses.update', $expense), $invalidData)
            ->assertRedirect(route('expenses.edit', $expense))
            ->assertSessionHasErrors(['title', 'description']);
    }

    public function test_expense_requires_all_fields(): void
    {
        $this->from(route('expenses.create'))
            ->post(route('expenses.store'), [])
            ->assertRedirect(route('expenses.create'))
            ->assertSessionHasErrors(['title', 'amount', 'date', 'description']);
    }

    public function test_expense_rejects_dates_before_today_when_creating_and_editing(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));
        $validData = [
            'title' => 'Makan siang',
            'amount' => 15000,
            'date' => '2026-11-02',
            'description' => 'Makan di kantin',
        ];

        $this->from(route('expenses.create'))
            ->post(route('expenses.store'), $validData)
            ->assertRedirect(route('expenses.create'))
            ->assertSessionHasErrors('date');

        $expense = $this->user->expenses()->create([
            ...$validData,
            'date' => '2026-11-03',
        ]);

        $this->from(route('expenses.edit', $expense))
            ->put(route('expenses.update', $expense), $validData)
            ->assertRedirect(route('expenses.edit', $expense))
            ->assertSessionHasErrors('date');

        $this->travelBack();
    }
}
