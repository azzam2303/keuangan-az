@extends('layouts.app')

@section('title', 'Rincian Dompet — AZ FINANCE')

@section('content')
<a href="{{ route('home') }}" class="back-home-link summary-back-link">
    <span aria-hidden="true">←</span> Kembali ke Beranda
</a>

<section class="wallet-transactions" aria-label="Rincian transaksi">
    <div class="wallet-transactions-heading">
        <div>
            <p class="eyebrow"><span aria-hidden="true">✦</span> riwayat keuangan</p>
            <h1>Semua transaksi</h1>
        </div>
    </div>

    <div class="wallet-transaction-groups">
        <section class="wallet-transaction-group" aria-labelledby="wallet-incomes-title">
            <h3 id="wallet-incomes-title">Pemasukan <span>{{ $incomes->count() }}</span></h3>
            @forelse($incomes as $income)
                <article class="wallet-transaction-item">
                    <div class="wallet-transaction-main">
                        <strong>{{ $income->title }}</strong>
                        <span>{{ $income->type === 'opening_balance' ? 'Uang awal' : 'Pendapatan' }}</span>
                    </div>
                    <strong class="wallet-transaction-amount wallet-income-amount">+ Rp {{ number_format($income->amount, 0, ',', '.') }}</strong>
                    <time datetime="{{ $income->date }}">{{ \Illuminate\Support\Carbon::parse($income->date)->format('d/m/Y') }}</time>
                    <p>{{ $income->description ?: 'Tidak ada keterangan' }}</p>
                </article>
            @empty
                <p class="wallet-transactions-empty">Belum ada data pemasukan.</p>
            @endforelse
        </section>

        <section class="wallet-transaction-group" aria-labelledby="wallet-expenses-title">
            <h3 id="wallet-expenses-title">Pengeluaran <span>{{ $expenses->count() }}</span></h3>
            @forelse($expenses as $expense)
                <article class="wallet-transaction-item">
                    <div class="wallet-transaction-main">
                        <strong>{{ $expense->title }}</strong>
                    </div>
                    <strong class="wallet-transaction-amount wallet-expense-amount">− Rp {{ number_format($expense->amount, 0, ',', '.') }}</strong>
                    <time datetime="{{ $expense->date }}">{{ \Illuminate\Support\Carbon::parse($expense->date)->format('d/m/Y') }}</time>
                    <p>{{ $expense->description ?: 'Tidak ada keterangan' }}</p>
                </article>
            @empty
                <p class="wallet-transactions-empty">Belum ada data pengeluaran.</p>
            @endforelse
        </section>
    </div>
</section>
@endsection
