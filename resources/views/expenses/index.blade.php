@extends('layouts.app')

@section('title', 'Pengeluaran Harian — AZ FINANCE')

@section('content')
<a href="{{ route('home') }}" class="back-home-link summary-back-link">
    <span aria-hidden="true">←</span> Kembali ke Beranda
</a>

<section class="page-heading finance-page-heading">
    <div>
        <p class="eyebrow"><span aria-hidden="true">✦</span> ringkasan dompet</p>
        <h1 class="page-title">Hai, yuk cek pengeluaranmu!</h1>
        <p class="page-description">Catat pengeluaranmu secara rutin supaya keuangan tetap terpantau dan rapi.</p>
    </div>
    <a href="{{ route('expenses.create') }}" class="button-primary">
        <span class="button-plus" aria-hidden="true">+</span>
        Tambah pengeluaran
    </a>
</section>

<section class="summary-card finance-summary-card finance-summary-expense" aria-label="Total pengeluaran">
    <div class="summary-copy">
        <p class="summary-label">Total pengeluaran keseluruhan</p>
        <p class="summary-amount" data-finance-total>Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
        <span class="summary-caption"><span aria-hidden="true">↗</span> rekap semua catatanmu</span>
    </div>
    <div class="summary-icon" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none">
            <path d="M7 16h34v22H7zM7 16l4-7h26l4 7" />
            <path d="M7 23h34M34 28h7" />
            <circle cx="30" cy="31" r="2" />
        </svg>
    </div>
</section>

<section class="chart-card" aria-labelledby="monthly-chart-title">
    <div class="chart-header">
        <div>
            <p class="eyebrow"><span aria-hidden="true">↗</span> tren harian</p>
            <h2 id="monthly-chart-title" class="chart-title">Pengeluaran bulan {{ $chartMonthLabel }}</h2>
            <p class="chart-description">Total pengeluaran per hari, termasuk hari tanpa transaksi.</p>
        </div>
    </div>

    <div class="chart-summary" aria-live="polite">
        <span><strong data-monthly-total>Rp {{ number_format($monthlyTotal, 0, ',', '.') }}</strong> total bulan ini</span>
        <span><strong data-days-with-transactions>{{ $daysWithExpenses }}</strong> hari ada pengeluaran</span>
    </div>

    <div class="chart-scroll">
        <svg class="monthly-chart" viewBox="0 0 1000 286" data-chart-maximum="{{ $chartMaximum }}" role="img" aria-label="Grafik pengeluaran harian bulan {{ $chartMonthLabel }}">
            <defs>
                <linearGradient id="expense-area-fill" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#a995d5" stop-opacity=".24" />
                    <stop offset="100%" stop-color="#a995d5" stop-opacity=".02" />
                </linearGradient>
            </defs>

            @for($tick = 0; $tick <= 4; $tick++)
                @php
                    $tickY = 238 - ($tick / 4) * 216;
                    $tickAmount = ($tick / 4) * $chartMaximum;
                @endphp
                <line x1="84" y1="{{ $tickY }}" x2="980" y2="{{ $tickY }}" class="chart-grid-line" />
                <text x="74" y="{{ $tickY + 4 }}" text-anchor="end" class="chart-axis-label">{{ number_format($tickAmount, 0, ',', '.') }}</text>
            @endfor

            <path d="{{ $chartLine }} L 980 238 L 84 238 Z" class="chart-area" />
            <path d="{{ $chartLine }}" class="chart-line" />

            @foreach($chartPoints as $point)
                <circle cx="{{ number_format($point['x'], 1, '.', '') }}" cy="{{ number_format($point['y'], 1, '.', '') }}" r="3.5" class="chart-point" data-chart-date="{{ $point['date']->toDateString() }}">
                    <title>{{ $point['date']->format('d M') }}: Rp {{ number_format($point['amount'], 0, ',', '.') }}</title>
                </circle>
                @if($point['showLabel'])
                    <text x="{{ number_format($point['x'], 1, '.', '') }}" y="265" text-anchor="middle" class="chart-axis-label">{{ $point['day'] }}</text>
                @endif
            @endforeach
        </svg>
    </div>
    <div class="chart-legend"><span class="chart-legend-mark" aria-hidden="true"></span> jumlah pengeluaran per hari</div>
    <nav class="chart-navigation" aria-label="Navigasi bulan grafik">
        <a href="{{ route('expenses.index', ['month' => $previousMonth]) }}" class="chart-nav-button" aria-label="Lihat bulan sebelumnya">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
            <span>Bulan sebelumnya</span>
        </a>
        <span class="chart-nav-month">{{ $chartMonthLabel }}</span>
        @if($nextMonth)
            <a href="{{ route('expenses.index', ['month' => $nextMonth]) }}" class="chart-nav-button chart-nav-next" aria-label="Lihat bulan berikutnya">
                <span>Bulan berikutnya</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
            </a>
        @else
            <button type="button" class="chart-nav-button chart-nav-next" disabled aria-label="Sudah di bulan terbaru">
                <span>Bulan berikutnya</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
            </button>
        @endif
    </nav>
</section>

<section class="table-card" aria-label="Daftar pengeluaran">
    <div class="alert-success transaction-delete-feedback" data-transaction-feedback role="status" hidden></div>
    <div class="table-scroll">
        <table class="expense-table">
            <thead>
                <tr>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Judul</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col" class="expense-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr data-transaction-row>
                        <td class="expense-date">{{ $expense->date }}</td>
                        <td class="expense-title">{{ $expense->title }}</td>
                        <td class="expense-amount">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                        <td class="expense-description">{{ $expense->description ?? '—' }}</td>
                        <td class="expense-actions">
                            <a href="{{ route('expenses.edit', $expense->id) }}" class="action-link">Edit</a>
                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="inline-form" data-delete-transaction>
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="month" value="{{ $month }}">
                                <button type="submit" class="action-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <div class="empty-illustration" aria-hidden="true">
                                <svg viewBox="0 0 40 40" fill="none">
                                    <path d="M11 5h14l6 6v24H11z" />
                                    <path d="M25 5v7h6M16 19h10M16 24h10M16 29h6" />
                                </svg>
                            </div>
                            <p class="empty-title">Belum ada catatan, nih!</p>
                            <p class="empty-copy">Yuk mulai catat pengeluaran pertamamu hari ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection