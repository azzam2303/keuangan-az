@extends('layouts.app')

@section('title', 'Tambah Pemasukan — AZ FINANCE')

@section('content')
<section class="form-card transaction-form-card transaction-form-income">
    <header class="transaction-form-banner">
        <div class="transaction-banner-copy">
            <span class="transaction-form-tag"><span aria-hidden="true">↗</span> PEMASUKAN</span>
            <h1>Tambah pemasukan</h1>
            <p>Catat uang awal dan pendapatan yang kamu terima.</p>
        </div>
        <div class="transaction-banner-icon" aria-hidden="true">
            <svg viewBox="0 0 64 64" fill="none"><path d="M12 20h40v31H12zM12 20l5-9h30l5 9" /><path d="M12 29h40M40 36h12" /><circle cx="36" cy="41" r="2" /><path d="m22 39 7-7 5 5 9-10M36 27h7v7" /></svg>
        </div>
        <span class="transaction-banner-orbit" aria-hidden="true"></span>
    </header>

    <div class="transaction-form-body">
    <header class="form-heading transaction-form-intro">
        <p class="eyebrow"><span aria-hidden="true">✦</span> detail catatan</p>
        <p class="page-description">Isi semua kolom di bawah ini untuk mencatat uang masuk.</p>
    </header>

    @if($errors->any())
        <div class="alert-success form-error-summary" role="alert">
            <span class="alert-icon" aria-hidden="true">!</span>
            <span>Yuk periksa lagi isian yang masih perlu diperbaiki.</span>
        </div>
    @endif

    <form action="{{ route('incomes.store') }}" method="POST" class="transaction-form">
        @csrf
        <div class="form-fields">
            <div class="form-field">
                <label for="type">Jenis pemasukan</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="opening_balance" @selected(old('type', 'income') === 'opening_balance')>Uang awal</option>
                    <option value="income" @selected(old('type', 'income') === 'income')>Pendapatan</option>
                </select>
                @error('type')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="title">Judul</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Contoh: Uang saku awal" pattern="[\p{L}\p{N} ]+" title="Hanya boleh berisi huruf, angka, dan spasi" required>
                @error('title')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="amount">Jumlah (Rp)</label>
                <input id="amount" type="number" name="amount" value="{{ old('amount') }}" class="form-control" min="0" step="1" placeholder="Contoh: 500000" required>
                @error('amount')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="date">Tanggal</label>
                <input id="date" type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" class="form-control" required>
                @error('date')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="description">Keterangan</label>
                <textarea id="description" name="description" class="form-control" placeholder="Contoh: Uang saku bulanan" pattern="[\p{L}\p{N} ]+" title="Hanya boleh berisi huruf, angka, dan spasi" required>{{ old('description') }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('incomes.index') }}" class="button-secondary">Batal</a>
            <button type="submit" class="button-primary">Simpan pemasukan</button>
        </div>
    </form>
    </div>
</section>
@endsection
