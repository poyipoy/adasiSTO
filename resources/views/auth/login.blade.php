<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — STO System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #111214 url('{{ asset('assets/images/adasi-login-bg.jpg') }}') center/cover no-repeat;
            color: #1f2933;
            font-size: 13px;
        }

        .login-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(17, 18, 20, 0.7);
            z-index: 0;
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
    </style>
</head>
<body>
    <div class="login-overlay"></div>
    <div class="login-wrapper">
        <div class="login-card">
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

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="contoh: admin" value="{{ old('username') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-login">
                    Sign In
                </button>
            </form>
        </div>

    </div>
</body>
</html>
