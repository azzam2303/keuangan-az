<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f6f3f1" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#17191f" media="(prefers-color-scheme: dark)">
    <title>@yield('title', 'AZ FINANCE — Pengeluaran Harian')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="page-decoration page-decoration--pink" aria-hidden="true"></div>
    <div class="page-decoration page-decoration--yellow" aria-hidden="true"></div>

    <nav class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <span class="brand-icon" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none">
                        <path d="M5 10.5h20a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a3 3 0 0 1-3-3v-14a3 3 0 0 1 3-3h17" />
                        <path d="M21 16h7v6h-7a3 3 0 0 1 0-6Z" />
                        <circle cx="21.5" cy="19" r=".8" />
                    </svg>
                </span>
                <span class="brand-copy">
                    <span class="brand-name">AZ FINANCE</span>
                    <span class="brand-caption">catatan kecil keuanganmu</span>
                </span>
            </div>
            <div class="topbar-account">
                <span class="topbar-note"><span aria-hidden="true">✦</span> kelola dengan ceria</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-button">Keluar</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="app-main">
        @if(session('success'))
            <div class="alert-success" role="status">
                <span class="alert-icon" aria-hidden="true">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')

        <footer class="page-footer">catatan rapi, pengeluaran lebih terkendali</footer>
    </main>
</body>
</html>
