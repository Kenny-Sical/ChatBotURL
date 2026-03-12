<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>

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

        .info-box {
            background-color: #f4f7fb;
            border: 1px solid #cfd9e7;
            border-left: 4px solid #7fa5d7;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #4a6a8a;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-9 col-md-7 col-lg-5 col-xl-4">

                <div class="card auth-card">

                    <!-- Cabecera -->
                    <div class="card-header py-4 text-white text-center">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="app-logo">
                                <img src="/favicon.png" alt="Logo">
                            </div>
                        </div>
                        <h4 class="mb-0 fw-bold">Recuperar Contraseña</h4>
                        <p class="mb-0 mt-1 opacity-75 small">Te enviaremos un enlace de recuperación</p>
                    </div>

                    <!-- Cuerpo -->
                    <div class="card-body p-4">

                        <!-- Mensaje informativo -->
                        <div class="info-box p-3 mb-4">
                            <i class="bi bi-info-circle-fill me-2" style="color: #2873b8;"></i>
                            Ingresa tu correo y recibirás las instrucciones para restablecer tu contraseña.
                        </div>

                        @if (session('status'))
                            <div class="alert border-0 rounded-3 py-2 px-3 mb-3" style="background-color:#f0f9f0; border-left: 4px solid #198754 !important; color: #155724;">
                                <i class="bi bi-check-circle-fill me-2" style="color:#198754;"></i>
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->has('email'))
                            <div class="alert alert-danger border-0 rounded-3 py-2 px-3 mb-3" style="background-color:#fff0f0; border-left: 4px solid #dc3545 !important;">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                {{ $errors->first('email') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <!-- Email -->
                            <div class="mb-4">
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
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Botón -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary-accent">
                                    <i class="bi bi-send-fill me-2"></i>
                                    Enviar enlace de recuperación
                                </button>
                            </div>

                        </form>

                    </div>

                    <!-- Pie -->
                    <div class="card-footer bg-white text-center pb-4 rounded-bottom">
                        <span class="small text-secondary">¿Recordaste tu contraseña?</span>
                        <a href="{{ route('login') }}" class="small link-accent ms-1">
                            Volver al inicio de sesión
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
