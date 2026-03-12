<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(160deg, #cfd9e7 0%, #7fa5d7 60%, #2873b8 100%);
            min-height: 100vh;
        }

        .login-card {
            border: 2px solid #cfd9e7;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(40, 115, 184, 0.25);
        }

        .login-card .card-header {
            background: #2873b8;
            border-radius: 1rem 1rem 0 0 !important;
            border-bottom: 3px solid #7fa5d7;
        }

        .login-card .card-body {
            background-color: #ffffff;
            border-radius: 0;
        }

        .form-control {
            border-color: #cfd9e7;
        }

        .form-control:focus {
            border-color: #7fa5d7;
            box-shadow: 0 0 0 0.2rem rgba(127, 165, 215, 0.3);
        }

        .input-group-text {
            background-color: #f4f7fb;
            border-color: #cfd9e7;
            color: #2873b8;
        }

        .form-check-input:checked {
            background-color: #2873b8;
            border-color: #2873b8;
        }

        .form-check-input:focus {
            border-color: #7fa5d7;
            box-shadow: 0 0 0 0.2rem rgba(127, 165, 215, 0.3);
        }

        .btn-login {
            background-color: #2873b8;
            border: 2px solid #7fa5d7;
            border-radius: 0.5rem;
            padding: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #2369c6;
            border-color: #2369c6;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(35, 105, 198, 0.35);
        }

        .link-accent {
            color: #2369c6;
            font-weight: 600;
            text-decoration: none;
        }

        .link-accent:hover {
            color: #2873b8;
            text-decoration: underline;
        }

        .btn-toggle-pass {
            border-color: #cfd9e7;
            color: #7fa5d7;
        }

        .btn-toggle-pass:hover {
            border-color: #7fa5d7;
            color: #2873b8;
        }

        .app-logo {
            width: 70px;
            height: 70px;
            background-color: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(207, 217, 231, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 4px;
        }

        .app-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .card-footer {
            border-top: 1px solid #cfd9e7 !important;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-9 col-md-7 col-lg-5 col-xl-4">

                <div class="card login-card">

                    <!-- Cabecera -->
                    <div class="card-header py-4 text-white text-center">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="app-logo">
                                <img src="/favicon.png" alt="Logo">
                            </div>
                        </div>
                        <h4 class="mb-0 fw-bold">ChatBot</h4>
                        <p class="mb-0 mt-1 opacity-75 small">Bienvenido de nuevo</p>
                    </div>

                    <!-- Cuerpo -->
                    <div class="card-body p-4">

                        @if (session('status'))
                            <div class="alert border-0 rounded-3 py-2 px-3 mb-3"
                                 style="background-color:#f0f9f0; border-left: 4px solid #198754 !important; color: #155724;">
                                <i class="bi bi-check-circle-fill me-2" style="color:#198754;"></i>
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-3 py-2 px-3 mb-3" style="background-color:#fff0f0; border-left: 4px solid #dc3545 !important;">
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.submit') }}">
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small text-secondary">
                                    Correo electrónico
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input
                                        type="email"
                                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                        id="email"
                                        name="email"
                                        placeholder="correo@ejemplo.com"
                                        value="{{ old('email') }}"
                                        autocomplete="email"
                                        autofocus
                                    >
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label fw-semibold small text-secondary mb-0">
                                        Contraseña
                                    </label>
                                    <a href="{{ route('password.request') }}" class="small link-accent">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                </div>
                                <div class="input-group mt-1">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="••••••••"
                                        autocomplete="current-password"
                                    >
                                    <button
                                        class="btn btn-outline-secondary btn-toggle-pass"
                                        type="button"
                                        id="togglePassword"
                                        tabindex="-1"
                                    >
                                        <i class="bi bi-eye-fill" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Recordarme -->
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label small text-secondary" for="remember">
                                    Recordar sesión
                                </label>
                            </div>

                            <!-- Botón -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login text-white">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Iniciar Sesión
                                </button>
                            </div>

                        </form>

                    </div>

                    <!-- Pie -->
                    <div class="card-footer bg-white text-center pb-4 rounded-bottom">
                        <span class="small text-secondary">¿No tienes cuenta?</span>
                        <a href="{{ route('register') }}" class="small link-accent ms-1">
                            Regístrate aquí
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Login -->
    <script src="{{ asset('js/auth/login.js') }}?v={{ $version }}"></script>

</body>
</html>
