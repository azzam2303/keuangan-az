@extends('layouts.app')

@section('title', 'Beranda — AZ FINANCE')

@section('content')
<section class="page-heading home-heading finance-page-heading">
    <div>
        <p class="eyebrow"><span aria-hidden="true">✦</span> ringkasan keuangan</p>
        <h1 class="page-title">Beranda</h1>
        <p class="page-description">Pantau uang masuk, pengeluaran, dan sisa dana kamu.</p>
    </div>
</section>

<section class="balance-card finance-balance" aria-label="Sisa dana">
    <div>
        <p class="balance-label">Sisa dana saat ini</p>
        <p class="balance-amount">Rp {{ number_format($balance, 0, ',', '.') }}</p>
        <p class="balance-note">Total pemasukan dikurangi total pengeluaran</p>
    </div>
    <a
        href="{{ route('wallet.transactions') }}"
        class="balance-decoration"
        aria-label="Lihat rincian pemasukan dan pengeluaran"
    >
        <svg viewBox="0 0 96 96" fill="none">
            <path d="M22 30h47a8 8 0 0 1 8 8v34a8 8 0 0 1-8 8H27a10 10 0 0 1-10-10V27a9 9 0 0 1 9-9h40" />
            <path d="M59 43h19v18H59a9 9 0 0 1 0-18Z" />
            <circle cx="61" cy="52" r="2" />
            <path d="M31 34h17M31 44h12" />
        </svg>
    </a>
    <div class="balance-breakdown">
        <div>
            <span class="breakdown-label"><span aria-hidden="true">↗</span> Pemasukan</span>
            <strong>Rp {{ number_format($totalIncome, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span class="breakdown-label"><span aria-hidden="true">↘</span> Pengeluaran</span>
            <strong>Rp {{ number_format($totalExpense, 0, ',', '.') }}</strong>
        </div>
    </div>
</section>

<section class="home-actions" aria-label="Pilih halaman keuangan">
    <a href="{{ route('incomes.index') }}" class="home-action home-action-income">
        <span class="home-action-icon" aria-hidden="true">
            <svg viewBox="0 0 32 32" fill="none"><path d="M7 23 23 7M9 7h14v14" /><path d="M5 27h22" /></svg>
        </span>
        <span class="home-action-copy">
            <strong>Pemasukan</strong>
            <span>Lihat grafik dan catat uang awal atau pendapatan</span>
        </span>
        <span class="home-action-arrow" aria-hidden="true">→</span>
    </a>
    <a href="{{ route('expenses.index') }}" class="home-action home-action-expense">
        <span class="home-action-icon" aria-hidden="true">
            <svg viewBox="0 0 32 32" fill="none"><path d="M7 9 23 25M9 25h14V11" /><path d="M5 5h22" /></svg>
        </span>
        <span class="home-action-copy">
            <strong>Pengeluaran</strong>
            <span>Lihat grafik dan catat uang yang sudah digunakan</span>
        </span>
        <span class="home-action-arrow" aria-hidden="true">→</span>
    </a>
</section>
@endsection
