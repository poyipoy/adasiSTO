<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — STO System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo-adasi.png') }}?v=2">
    <link rel="stylesheet" href="{{ asset('vendor/@fontsource/inter/index.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/adasi-splash.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f2933;
            font-size: 13px;
        }

        .login-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #111214 url('{{ asset('assets/images/adasi-login-bg.jpg') }}') center/cover no-repeat;
            z-index: -2;
        }

        .login-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(17, 18, 20, 0.7);
            z-index: -1;
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            padding: 40px 32px;
        }

        .brand {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand img {
            height: 48px;
            margin-bottom: 12px;
        }

        .brand h1 {
            font-size: 15px;
            font-weight: 700;
            color: #1d252c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .brand p {
            font-size: 12px;
            color: #808b99;
            margin-top: 2px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #525e6c;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-control {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #bfc4ce;
            border-radius: 2px;
            font-size: 13px;
            font-family: inherit;
            color: #252a31;
            transition: border-color 0.15s;
            outline: none;
            background: #fff;
        }

        .form-control:focus {
            border-color: #0072ce;
            box-shadow: 0 0 0 1px rgba(0,114,206,0.15);
        }

        .form-control::placeholder { color: #a0aabf; }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #a0aabf;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
            outline: none;
            transition: color 0.15s;
        }

        .password-toggle:hover {
            color: #525e6c;
        }

        .password-container .form-control {
            padding-right: 36px;
        }

        .btn-login {
            width: 100%;
            padding: 10px;
            background: #0072ce;
            border: 1px solid #005fa8;
            border-radius: 2px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            background: #005fa8;
        }

        .error-alert {
            background: #ffcdd2;
            border: 1px solid #ef5350;
            color: #b71c1c;
            padding: 8px 10px;
            border-radius: 2px;
            margin-bottom: 16px;
            font-size: 12px;
            text-align: center;
            font-weight: 500;
        }

        .support-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #bcc5d0;
            font-size: 11px;
            text-decoration: none;
        }
        .support-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        .forgot-password {
            display: block;
            text-align: right;
            margin-top: 6px;
            font-size: 11px;
            color: #0072ce;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .contact-admin-card {
            display: none;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            padding: 40px 32px;
            text-align: center;
        }

        .contact-icon {
            width: 48px;
            height: 48px;
            background: #eef2f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #0072ce;
        }

        .contact-title {
            font-size: 15px;
            font-weight: 700;
            color: #1d252c;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .contact-desc {
            font-size: 13px;
            color: #525e6c;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .btn-back {
            background: transparent;
            border: 1px solid #bfc4ce;
            color: #525e6c;
        }

        .btn-back:hover {
            background: #f5f7f9;
        }
    </style>
</head>
<body>
    @include('partials.adasi-splash')

    <div class="login-bg"></div>
    <div class="login-overlay"></div>
    <div class="login-wrapper">
        <div class="login-card" id="loginCard">
            <div class="brand">
                <img src="{{ asset('assets/images/logo-adasi.png') }}" alt="ADASI Logo">
                <h1>STO System</h1>
                <p>Scan To Office</p>
            </div>

            @if($errors->any())
            <div class="error-alert">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.store', [], false) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="contoh: admin" value="{{ old('username') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1" aria-label="Toggle password visibility">
                            <svg id="eye-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <a href="#" class="forgot-password" onclick="showContactAdmin(event)">Lupa Password?</a>
                </div>

                <button type="submit" class="btn-login">
                    Sign In
                </button>
            </form>
        </div>

        <div class="contact-admin-card" id="contactAdminCard">
            <div class="contact-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="contact-title">Lupa Password?</h2>
            <p class="contact-desc">Silakan hubungi Administrator IT atau atasan Anda untuk melakukan perubahan atau reset password.</p>
            <button class="btn-login btn-back" onclick="showLogin()">Kembali ke Login</button>
        </div>

    </div>

    <script>
        function showContactAdmin(e) {
            e.preventDefault();
            document.getElementById('loginCard').style.display = 'none';
            document.getElementById('contactAdminCard').style.display = 'block';
        }

        function showLogin() {
            document.getElementById('contactAdminCard').style.display = 'none';
            document.getElementById('loginCard').style.display = 'block';
        }

        function togglePassword() {
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />`;
            } else {
                pwdInput.type = 'password';
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;
            }
        }
    </script>
</body>
</html>
