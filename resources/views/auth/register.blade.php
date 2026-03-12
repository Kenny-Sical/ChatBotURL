<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>

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

        .auth-card {
            border: 2px solid #cfd9e7;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(40, 115, 184, 0.25);
        }

        .auth-card .card-header {
            background: #2873b8;
            border-radius: 1rem 1rem 0 0 !important;
            border-bottom: 3px solid #7fa5d7;
        }

        .auth-card .card-body {
            background-color: #ffffff;
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

        .btn-primary-accent {
            background-color: #2873b8;
            border: 2px solid #7fa5d7;
            border-radius: 0.5rem;
            padding: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            color: #fff;
        }

        .btn-primary-accent:hover {
            background-color: #2369c6;
            border-color: #2369c6;
            color: #fff;
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

        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-4">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">

                <div class="card auth-card">

                    <!-- Cabecera -->
                    <div class="card-header py-4 text-white text-center">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="app-logo">
                                <img src="/favicon.png" alt="Logo">
                            </div>
                        </div>
                        <h4 class="mb-0 fw-bold">Crear Cuenta</h4>
                        <p class="mb-0 mt-1 opacity-75 small">Completa tus datos para registrarte</p>
                    </div>

                    <!-- Cuerpo -->
                    <div class="card-body p-4">

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-3 py-2 px-3 mb-3" style="background-color:#fff0f0; border-left: 4px solid #dc3545 !important;">
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                            @csrf

                            <!-- Nombre completo -->
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold small text-secondary">
                                    Nombre completo
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                        id="name"
                                        name="name"
                                        placeholder="Tu nombre completo"
                                        value="{{ old('name') }}"
                                        autocomplete="name"
                                        autofocus
                                        required
                                    >
                                </div>
                            </div>

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
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-1">
                                <label for="password" class="form-label fw-semibold small text-secondary">
                                    Contraseña
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Mínimo 8 caracteres"
                                        autocomplete="new-password"
                                        required
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

                            <!-- Barra de fortaleza -->
                            <div class="mb-3 mt-2">
                                <div class="d-flex gap-1">
                                    <div class="password-strength flex-fill bg-secondary opacity-25" id="bar1"></div>
                                    <div class="password-strength flex-fill bg-secondary opacity-25" id="bar2"></div>
                                    <div class="password-strength flex-fill bg-secondary opacity-25" id="bar3"></div>
                                    <div class="password-strength flex-fill bg-secondary opacity-25" id="bar4"></div>
                                </div>
                                <small class="text-secondary mt-1 d-block" id="strengthLabel"></small>
                            </div>

                            <!-- Confirmar contraseña -->
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold small text-secondary">
                                    Confirmar contraseña
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-check-fill"></i>
                                    </span>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        placeholder="Repite tu contraseña"
                                        autocomplete="new-password"
                                        required
                                    >
                                </div>
                                <small class="text-danger mt-1 d-none" id="matchError">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>Las contraseñas no coinciden.
                                </small>
                            </div>

                            <!-- Términos y condiciones -->
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label small text-secondary" for="terms">
                                    Acepto los
                                    <a href="#" class="link-accent">términos y condiciones</a>
                                </label>
                            </div>

                            <!-- Botón -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary-accent">
                                    <i class="bi bi-person-check-fill me-2"></i>
                                    Crear cuenta
                                </button>
                            </div>

                        </form>

                    </div>

                    <!-- Pie -->
                    <div class="card-footer bg-white text-center pb-4 rounded-bottom">
                        <span class="small text-secondary">¿Ya tienes una cuenta?</span>
                        <a href="{{ route('login') }}" class="small link-accent ms-1">
                            Inicia sesión aquí
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Register -->
    <script src="{{ asset('js/auth/register.js') }}?v={{ $version }}"></script>

</body>
</html>
