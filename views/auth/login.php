<?php
session_start();
// Si ya está autenticado, redirigir según rol
if (!empty($_SESSION['user_id'])) {
    $redir = match ($_SESSION['user_rol']) {
        'superadmin', 'admin_sede' => '/mediconnect/views/admin/dashboard.php',
        'prestador'                => '/mediconnect/views/prestador/dashboard.php',
        default                    => '/mediconnect/views/cliente/dashboard.php',
    };
    header("Location: $redir");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · MediConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/main.css">
</head>

<body>

    <div class="auth-wrapper">
        <div class="card auth-card p-4">

            <!-- Logo -->
            <div class="auth-logo">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z" />
                </svg>
            </div>

            <h4 class="text-center fw-bold mb-1">MediConnect</h4>
            <p class="text-center text-muted small mb-4">Inicia sesión para continuar</p>

            <!-- Alerta de error -->
            <div id="alert-error" class="alert alert-danger d-none py-2 small" role="alert"></div>

            <!-- Formulario -->
            <form id="form-login" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold small" for="email">Correo electrónico</label>
                    <input type="email" id="email" class="form-control"
                        placeholder="correo@ejemplo.com" required autocomplete="email">
                    <div class="invalid-feedback">Ingresa un correo válido.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small" for="password">Contraseña</label>
                    <div class="input-group">
                        <input type="password" id="password" class="form-control"
                            placeholder="••••••••" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="toggle-pass"
                            tabindex="-1" title="Mostrar/ocultar contraseña">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6z" />
                            </svg>
                        </button>
                    </div>
                    <div class="invalid-feedback">La contraseña es obligatoria.</div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-mc" id="btn-login">
                        <span id="btn-text">Iniciar sesión</span>
                        <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                </div>
            </form>

            <hr class="my-3">
            <p class="text-center small mb-0">
                ¿No tienes cuenta?
                <a href="/mediconnect/views/auth/registro.php" class="fw-semibold">Regístrate</a>
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/mediconnect/assets/js/auth.js"></script>
</body>

</html>