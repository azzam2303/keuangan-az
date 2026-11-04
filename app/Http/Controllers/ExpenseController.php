<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = $request->user()->expenses()->orderBy('date', 'desc')->get();
        $totalExpense = $expenses->sum('amount');

        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);
        $month = $validated['month'] ?? now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $currentMonthStart = now()->startOfMonth();
        $previousMonth = $monthStart->copy()->subMonth()->format('Y-m');
        $nextMonthStart = $monthStart->copy()->addMonth();
        $nextMonth = $nextMonthStart->lt($currentMonthStart)
            ? $nextMonthStart->format('Y-m')
            : ($monthStart->lt($currentMonthStart) ? $currentMonthStart->format('Y-m') : null);
        $dailyTotals = $request->user()->expenses()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $daysInMonth = $monthStart->daysInMonth;
        $monthlyTotal = $dailyTotals->sum();
        $daysWithExpenses = $dailyTotals->filter(fn ($total) => (float) $total > 0)->count();
        $maxDailyTotal = (float) $dailyTotals->max();
        $chartStep = $maxDailyTotal > 0
            ? 10 ** floor(log10($maxDailyTotal / 4))
            : 1;
        $chartMaximum = $maxDailyTotal > 0
            ? ceil($maxDailyTotal / (4 * $chartStep)) * 4 * $chartStep
            : 4;
        $chartPoints = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $monthStart->copy()->day($day);
            $amount = (float) ($dailyTotals[$date->toDateString()] ?? 0);
            $chartPoints[] = [
                'day' => $day,
                'date' => $date,
                'amount' => $amount,
                'x' => 84 + (($day - 1) / max($daysInMonth - 1, 1)) * 896,
                'y' => 238 - ($amount / $chartMaximum) * 216,
                'showLabel' => $day === 1 || $day % 5 === 0 || $day === $daysInMonth,
            ];
        }

        $chartLine = collect($chartPoints)
            ->map(fn ($point, $index) => ($index === 0 ? 'M' : 'L')
                . number_format($point['x'], 1, '.', '')
                . ' '
                . number_format($point['y'], 1, '.', ''))
            ->implode(' ');
        $chartMonthLabel = $monthStart->locale('id')->translatedFormat('F Y');

        return view('expenses.index', compact(
            'expenses',
            'totalExpense',
            'month',
            'previousMonth',
            'nextMonth',
            'chartMonthLabel',
            'daysInMonth',
            'monthlyTotal',
            'daysWithExpenses',
            'chartMaximum',
            'chartPoints',
            'chartLine',
        ));
    }

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $request->user()->expenses()->create($validated);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    public function edit(Request $request, Expense $expense)
    {
        $expense = $request->user()->expenses()->findOrFail($expense->id);

        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $expense = $request->user()->expenses()->findOrFail($expense->id);
        $validated = $request->validate($this->rules(), $this->messages());

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $expense = $request->user()->expenses()->findOrFail($expense->id);
        $month = $request->expectsJson()
            ? $request->validate([
                'month' => ['nullable', 'date_format:Y-m'],
            ])['month'] ?? now()->format('Y-m')
            : null;
        $expense->delete();

        if ($month !== null) {
            $monthStart = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
            $dailyTotals = $request->user()->expenses()
                ->whereBetween('date', [$monthStart->toDateString(), $monthStart->copy()->endOfMonth()->toDateString()])
                ->selectRaw('date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            return response()->json([
                'message' => 'Pengeluaran berhasil dihapus.',
                'total' => (float) $request->user()->expenses()->sum('amount'),
                'monthlyTotal' => (float) $dailyTotals->sum(),
                'daysWithTransactions' => (int) $dailyTotals->filter(fn ($total) => (float) $total > 0)->count(),
                'dailyTotals' => $dailyTotals->map(fn ($total) => (float) $total),
            ]);
        }

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dihapus!');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN ]+$/u'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'description' => ['required', 'string', 'regex:/^[\pL\pN ]+$/u'],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.regex' => 'Judul hanya boleh berisi huruf, angka, dan spasi.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
            'description.required' => 'Keterangan wajib diisi.',
            'description.regex' => 'Keterangan hanya boleh berisi huruf, angka, dan spasi.',
        ];
    }
}