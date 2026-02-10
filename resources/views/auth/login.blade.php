<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        .login-wrap { max-width: 420px; margin: 60px auto; padding: 0 16px; }
        .login-card { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .login-title { margin: 0 0 14px; font-size: 20px; }
        .login-sub { margin: 0 0 18px; color: #555; font-size: 14px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display:block; margin-bottom: 6px; font-weight: 600; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .login-actions { display:flex; gap: 10px; align-items:center; margin-top: 16px; }
        .error-box { background: #fde8e8; border: 1px solid #f5c2c2; color: #842029; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .remember { display:flex; gap:8px; align-items:center; font-size: 14px; color:#333; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <h1 class="login-title">Iniciar sesión</h1>
            <p class="login-sub">Accede al sistema con tu correo y contraseña.</p>

            @if($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" required autocomplete="username" autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                </div>

                <div class="login-actions">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Entrar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
