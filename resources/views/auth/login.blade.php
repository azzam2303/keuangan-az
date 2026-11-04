<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#258ee8">
    <title>Login — AZ FINANCE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-art" aria-label="Ilustrasi keamanan akun">
            <div class="login-art-glow login-art-glow-one" aria-hidden="true"></div>
            <div class="login-art-glow login-art-glow-two" aria-hidden="true"></div>
            <svg class="login-illustration" viewBox="0 0 360 330" fill="none" role="img" aria-label="Ponsel dengan gembok">
                <path d="M37 110C94 34 174 158 250 78c25-26 40-30 65-19" class="login-flight-path" />
                <path d="m306 49 23 8-20 14-3-22Z" class="login-paper-plane" />
                <g transform="rotate(-8 143 193)">
                    <rect x="69" y="51" width="151" height="276" rx="27" class="login-phone-frame" />
                    <rect x="79" y="67" width="131" height="247" rx="18" class="login-phone-screen" />
                    <rect x="119" y="58" width="50" height="5" rx="2.5" class="login-phone-speaker" />
                    <circle cx="144" cy="292" r="7" class="login-phone-home" />
                    <path d="M126 184v-14a18 18 0 0 1 36 0v14" class="login-lock-shackle" />
                    <rect x="117" y="179" width="54" height="48" rx="10" class="login-lock-body" />
                    <circle cx="144" cy="197" r="5" class="login-lock-keyhole" />
                    <path d="M144 201v9" class="login-lock-keyhole" />
                </g>
                <circle cx="274" cy="257" r="23" class="login-art-sparkle" />
                <path d="m274 243 4 10 10 4-10 4-4 10-4-10-10-4 10-4 4-10Z" class="login-art-star" />
            </svg>
            <div class="login-art-caption">
                <span class="login-art-caption-icon" aria-hidden="true">✦</span>
                <span>Keuangan rapi, hati lebih tenang</span>
            </div>
        </section>

        <section class="login-panel">
            <a href="{{ route('login') }}" class="login-brand" aria-label="AZ FINANCE">
                <span class="login-brand-icon" aria-hidden="true">AZ</span>
                <span>AZ FINANCE</span>
            </a>

            <div class="login-content">
                <p class="login-eyebrow">SELAMAT DATANG KEMBALI</p>
                <h1>Login</h1>
                <p class="login-description">Masuk untuk melanjutkan catatan keuanganmu.</p>

                @if(session('success'))
                    <div class="login-success" role="status">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="login-error" role="alert">
                        <span class="login-error-icon" aria-hidden="true">!</span>
                        <div>
                            <strong>Login belum berhasil</strong>
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST" class="login-form">
                    @csrf
                    <div class="login-field">
                        <label for="username">Username</label>
                        <div class="login-input-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4" /><path d="M5 20a7 7 0 0 1 14 0" /></svg>
                            <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" required autofocus>
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="password">Kata sandi</label>
                        <div class="login-input-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="3" /><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" /></svg>
                            <input id="password" type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /><path data-eye-slash d="m4 4 16 16" hidden /></svg>
                            </button>
                        </div>
                    </div>

                    <label class="login-remember">
                        <input type="checkbox" name="remember" value="1">
                        <span>Ingat saya</span>
                    </label>

                    <button type="submit" class="login-submit">Masuk <span aria-hidden="true">→</span></button>
                </form>

                <p class="login-register-link">Belum punya akun? <a href="{{ route('register') }}">Buat akun baru</a></p>
                <p class="login-footer-note"><span aria-hidden="true">🔒</span> Data keuanganmu tersimpan dengan aman</p>
            </div>
        </section>
    </main>
</body>
</html>
