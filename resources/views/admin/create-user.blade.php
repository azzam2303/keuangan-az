@extends('layouts.app')

@section('title', 'Daftar Akun — AZ FINANCE')

@section('content')
<section class="form-card">
    <header class="form-heading">
        <p class="eyebrow"><span aria-hidden="true">✦</span> khusus administrator</p>
        <h1 class="page-title">Daftar akun baru</h1>
        <p class="page-description">Buatkan akun agar pengguna bisa mulai mencatat keuangannya.</p>
    </header>

    @if($errors->any())
        <div class="alert-success form-error-summary" role="alert">
            <span class="alert-icon" aria-hidden="true">!</span>
            <span>Periksa kembali data akun yang diisi.</span>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="form-fields">
            <div class="form-field">
                <label for="username">Username</label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    class="form-control"
                    placeholder="Buat username"
                    minlength="3"
                    maxlength="30"
                    autocomplete="username"
                    required
                    autofocus
                >
                @error('username')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="password">Kata sandi</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Minimal 8 karakter"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">Konfirmasi kata sandi</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Ulangi kata sandi"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.index') }}" class="button-secondary">Batal</a>
            <button type="submit" class="button-primary">Buat akun</button>
        </div>
    </form>
</section>
@endsection
