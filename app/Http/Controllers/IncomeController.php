<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $incomes = $request->user()->incomes()->orderBy('date', 'desc')->get();
        $totalIncome = $incomes->sum('amount');

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
        $dailyTotals = $request->user()->incomes()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $daysInMonth = $monthStart->daysInMonth;
        $monthlyTotal = $dailyTotals->sum();
        $daysWithIncome = $dailyTotals->filter(fn ($total) => (float) $total > 0)->count();
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

        return view('incomes.index', compact(
            'incomes',
            'totalIncome',
            'month',
            'previousMonth',
            'nextMonth',
            'chartMonthLabel',
            'daysInMonth',
            'monthlyTotal',
            'daysWithIncome',
            'chartMaximum',
            'chartPoints',
            'chartLine',
        ));
    }

    public function create()
    {
        return view('incomes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $request->user()->incomes()->create($validated);

        return redirect()->route('incomes.index')->with('success', 'Pemasukan berhasil ditambahkan!');
    }

    public function edit(Request $request, Income $income)
    {
        $income = $request->user()->incomes()->findOrFail($income->id);

        return view('incomes.edit', compact('income'));
    }

    public function update(Request $request, Income $income)
    {
        $income = $request->user()->incomes()->findOrFail($income->id);
        $validated = $request->validate($this->rules(), $this->messages());

        $income->update($validated);

        return redirect()->route('incomes.index')->with('success', 'Pemasukan berhasil diperbarui!');
    }

    public function destroy(Request $request, Income $income)
    {
        $income = $request->user()->incomes()->findOrFail($income->id);
        $month = $request->expectsJson()
            ? $request->validate([
                'month' => ['nullable', 'date_format:Y-m'],
            ])['month'] ?? now()->format('Y-m')
            : null;
        $income->delete();

        if ($month !== null) {
            $monthStart = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
            $dailyTotals = $request->user()->incomes()
                ->whereBetween('date', [$monthStart->toDateString(), $monthStart->copy()->endOfMonth()->toDateString()])
                ->selectRaw('date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            return response()->json([
                'message' => 'Pemasukan berhasil dihapus.',
                'total' => (float) $request->user()->incomes()->sum('amount'),
                'monthlyTotal' => (float) $dailyTotals->sum(),
                'daysWithTransactions' => (int) $dailyTotals->filter(fn ($total) => (float) $total > 0)->count(),
                'dailyTotals' => $dailyTotals->map(fn ($total) => (float) $total),
            ]);
        }

        return redirect()->route('incomes.index')->with('success', 'Pemasukan berhasil dihapus!');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN ]+$/u'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'description' => ['required', 'string', 'regex:/^[\pL\pN ]+$/u'],
            'type' => ['required', 'in:opening_balance,income'],
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
            'type.required' => 'Jenis pemasukan wajib dipilih.',
        ];
    }
}
