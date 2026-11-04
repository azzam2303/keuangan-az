@extends('layouts.app')

@section('title', 'Edit Pengeluaran — AZ FINANCE')

@section('content')
<section class="form-card transaction-form-card transaction-form-expense">
    <header class="transaction-form-banner">
        <div class="transaction-banner-copy">
            <span class="transaction-form-tag"><span aria-hidden="true">↘</span> PENGELUARAN</span>
            <h1>Edit pengeluaran</h1>
            <p>Perbarui catatan pengeluaranmu agar tetap akurat.</p>
        </div>
        <div class="transaction-banner-icon" aria-hidden="true">
            <svg viewBox="0 0 64 64" fill="none"><path d="M12 20h40v31H12zM12 20l5-9h30l5 9" /><path d="M12 29h40M40 36h12" /><circle cx="36" cy="41" r="2" /></svg>
        </div>
        <span class="transaction-banner-orbit" aria-hidden="true"></span>
    </header>

    <div class="transaction-form-body">
    <header class="form-heading transaction-form-intro">
        <p class="eyebrow"><span aria-hidden="true">✦</span> rapikan catatan</p>
        <p class="page-description">Periksa kembali detail sebelum menyimpan perubahan.</p>
    </header>

    @if($errors->any())
        <div class="alert-success form-error-summary" role="alert">
            <span class="alert-icon" aria-hidden="true">!</span>
            <span>Yuk periksa lagi isian yang masih perlu diperbaiki.</span>
        </div>
    @endif

    <form action="{{ route('expenses.update', $expense->id) }}" method="POST" class="transaction-form">
        @csrf
        @method('PUT')
        <div class="form-fields">
            <div class="form-field">
                <label for="title">Judul pengeluaran</label>
                <input id="title" type="text" name="title" value="{{ old('title', $expense->title) }}" class="form-control" pattern="[\p{L}\p{N} ]+" title="Hanya boleh berisi huruf, angka, dan spasi" required>
                @error('title')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="amount">Jumlah (Rp)</label>
                <input id="amount" type="number" name="amount" value="{{ old('amount', $expense->amount) }}" class="form-control" min="0" step="1" required>
                @error('amount')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field expense-date-field">
                <label for="date">Tanggal</label>
                <input id="date" type="date" name="date" value="{{ old('date', $expense->date) }}" min="{{ date('Y-m-d') }}" class="form-control" required>
                @error('date')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="description">Keterangan</label>
                <textarea id="description" name="description" class="form-control" pattern="[\p{L}\p{N} ]+" title="Hanya boleh berisi huruf, angka, dan spasi" required>{{ old('description', $expense->description) }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('expenses.index') }}" class="button-secondary">Batal</a>
            <button type="submit" class="button-primary">Simpan perubahan</button>
        </div>
    </form>
    </div>
</section>
@endsection