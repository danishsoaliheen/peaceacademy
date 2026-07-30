<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Peace Academy ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e2e8f0;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: #1e293b;
            border-radius: 16px;
            padding: 40px 36px 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,.4);
            border: 1px solid rgba(255,255,255,.06);
        }

        .login-brand {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-brand .logo-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #3b82f6, #0ea5e9);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            margin-bottom: 14px;
            box-shadow: 0 6px 20px rgba(59,130,246,.3);
        }
        .login-brand h1 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: .3px;
        }
        .login-brand p {
            font-size: .78rem;
            color: #64748b;
            margin-top: 4px;
        }

        .form-label {
            font-size: .78rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        .form-control {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 9px;
            color: #e2e8f0;
            padding: 11px 14px;
            font-size: .88rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            background: #0f172a;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
            color: #e2e8f0;
        }
        .form-control::placeholder { color: #475569; }

        .btn-login {
            background: linear-gradient(135deg, #3b82f6, #0ea5e9);
            color: #fff !important;
            border: none;
            border-radius: 9px;
            padding: 12px;
            font-weight: 700;
            font-size: .92rem;
            width: 100%;
            cursor: pointer;
            transition: opacity .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover { opacity: .88; color: #fff !important; }

        .form-check-label {
            font-size: .8rem;
            color: #94a3b8;
        }
        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .alert-danger-custom {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            color: #fca5a5;
            border-radius: 9px;
            padding: 10px 14px;
            font-size: .82rem;
            margin-bottom: 18px;
        }
        .alert-success-custom {
            background: rgba(34,197,94,.1);
            border: 1px solid rgba(34,197,94,.3);
            color: #86efac;
            border-radius: 9px;
            padding: 10px 14px;
            font-size: .82rem;
            margin-bottom: 18px;
        }
        .invalid-feedback { font-size: .76rem; }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: .7rem;
            color: #475569;
        }

        .input-icon-wrapper {
            position: relative;
        }
        .input-icon-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: .82rem;
        }
        .input-icon-wrapper .form-control {
            padding-left: 40px;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-brand">
            <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
            <h1>Peace Academy</h1>
            <p>ERP Management System</p>
        </div>

        @if ($errors->any())
            <div class="alert-danger-custom">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-danger-custom">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert-success-custom">
                <i class="fas fa-check-circle me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="admin@peaceacademy.com"
                           required
                           autofocus
                           autocomplete="email">
                </div>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Enter your password"
                           required
                           autocomplete="current-password">
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} Peace Academy. All rights reserved.
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
