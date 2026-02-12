<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="login-page">
    <div class="login-shell">
        <div class="login-layout">
            <div class="login-left">
                <div class="login-left-content">
                    <h1 class="login-hero-title">Bienvenido</h1>
                    <p class="login-hero-sub">
                        Inventario y sistema de facturación<br>
                        <strong>Micheladas la 66</strong>
                    </p>
                    <div class="login-hero-note">
                        Inicia sesión para continuar.
                    </div>
                </div>
            </div>

            <div class="login-right">
                <div class="login-panel">
                    <h2 class="login-title">Iniciar sesión</h2>
                    <p class="login-sub">Ingrese su usuario y contraseña</p>

                    @if($errors->any())
                        <div class="login-error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.submit') }}">
                        @csrf

                        <div class="form-group">
                            <label for="username" class="login-label">Usuario</label>
                            <input id="username" name="username" type="text" value="{{ old('username') }}" required autocomplete="username" autofocus>
                        </div>

                        <div class="form-group">
                            <label for="password" class="login-label">Contraseña</label>
                            <input id="password" name="password" type="password" required autocomplete="current-password">
                        </div>

                        <div class="login-actions">
                            <button type="submit" class="btn btn-secondary" style="flex: 1;">Aceptar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
