<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /mediconnect/views/cliente/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta · MediConnect</title>
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

            <h4 class="text-center fw-bold mb-1">Crear cuenta</h4>
            <p class="text-center text-muted small mb-4">Completa el formulario para registrarte</p>

            <!-- Alerta -->
            <div id="alert-box" class="alert d-none py-2 small" role="alert"></div>

            <!-- Formulario -->
            <form id="form-registro" novalidate>

                <div class="mb-3">
                    <label class="form-label fw-semibold small" for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" class="form-control"
                        placeholder="Ej: Juan Pérez" required minlength="3" autocomplete="name">
                    <div class="invalid-feedback">Ingresa tu nombre completo (mín. 3 caracteres).</div>
                </div>

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
                            placeholder="Mínimo 8 caracteres" required minlength="8"
                            autocomplete="new-password">
                        <button class="btn btn-outline-secondary" type="button" id="toggle-pass" tabindex="-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6z" />
                            </svg>
                        </button>
                    </div>
                    <!-- Barra de fortaleza -->
                    <div class="progress mt-2" style="height:5px;">
                        <div id="strength-bar" class="progress-bar" style="width:0%;transition:.3s"></div>
                    </div>
                    <div id="strength-label" class="form-text"></div>
                    <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small" for="confirm">Confirmar contraseña</label>
                    <input type="password" id="confirm" class="form-control"
                        placeholder="Repite tu contraseña" required autocomplete="new-password">
                    <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-mc" id="btn-registro">
                        <span id="btn-text">Crear cuenta</span>
                        <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                </div>
            </form>

            <hr class="my-3">
            <p class="text-center small mb-0">
                ¿Ya tienes cuenta?
                <a href="/mediconnect/views/auth/login.php" class="fw-semibold">Inicia sesión</a>
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/mediconnect/assets/js/auth.js"></script>
</body>

</html>