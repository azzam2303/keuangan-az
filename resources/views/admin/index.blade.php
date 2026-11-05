@extends('layouts.app')

@section('title', 'Admin — AZ FINANCE')

@section('content')
<section class="page-heading finance-page-heading">
    <div>
        <p class="eyebrow"><span aria-hidden="true">✦</span> khusus administrator</p>
        <h1 class="page-title">Kelola akun</h1>
        <p class="page-description">Reset catatan keuangan atau hapus akun pengguna.</p>
    </div>
</section>

<section class="table-card" aria-label="Daftar akun pengguna">
    <div class="alert-success transaction-delete-feedback" data-admin-feedback role="status" hidden></div>
    <div class="table-scroll">
        <table class="expense-table">
            <thead>
                <tr>
                    <th>Nama akun</th>
                    <th>Username</th>
                    <th>Catatan keuangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody data-admin-users>
                @forelse($users as $user)
                    <tr data-admin-user-row>
                        <td class="expense-title">{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td data-account-finance-counts>{{ $user->incomes_count }} pemasukan · {{ $user->expenses_count }} pengeluaran</td>
                        <td class="expense-actions admin-actions">
                            <form
                                action="{{ route('admin.users.reset', $user) }}"
                                method="POST"
                                class="inline-form"
                                data-admin-action="reset"
                                data-confirm="Reset semua data keuangan akun {{ $user->username }}? Tindakan ini tidak dapat dibatalkan."
                                onsubmit="return false"
                            >
                                @csrf
                                <button type="submit" class="action-link">Reset data</button>
                            </form>
                            <form
                                action="{{ route('admin.users.destroy', $user) }}"
                                method="POST"
                                class="inline-form"
                                data-admin-action="delete"
                                data-confirm="Hapus akun {{ $user->username }} beserta semua datanya? Tindakan ini tidak dapat dibatalkan."
                                onsubmit="return false"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-delete">Hapus akun</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state" data-admin-empty>Belum ada akun pengguna lain.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
