<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/paths.php';
$w = mc_web_base();

session_start();
if (!empty($_SESSION['user_id'])) {
    $redir = match ($_SESSION['user_rol']) {
        'superadmin', 'admin_sede' => $w . '/views/admin/dashboard.php',
        'prestador'                => $w . '/views/prestador/dashboard.php',
        default                    => $w . '/views/cliente/dashboard.php',
    };
    header('Location: ' . $redir);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MediConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --teal:       #0a9396;
            --teal-dark:  #005f73;
            --teal-light: #94d2bd;
            --cream: #d0dad7;
            --sand: #d7d7c8;
            --coral:      #ee9b00;
            --radius:     1rem;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--cream);
            overflow: hidden;
        }

        /* ── Panel izquierdo decorativo ── */
        .auth-panel {
            width: 42%;
            background: linear-gradient(160deg, var(--teal-dark) 0%, var(--teal) 60%, var(--teal-light) 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 3rem;
            overflow: hidden;
        }

        .auth-panel::before {
            content: '';
            position: absolute;
            width: 420px; height: 420px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            top: -80px; left: -80px;
        }

        .auth-panel::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            bottom: 120px; right: -60px;
        }

        .panel-cross {
            position: absolute;
            top: 2.5rem; left: 2.5rem;
            width: 52px; height: 52px;
        }

        .panel-cross span {
            position: absolute;
            background: rgba(255,255,255,.9);
            border-radius: 4px;
        }

        .panel-cross span:nth-child(1) {
            width: 52px; height: 14px;
            top: 19px; left: 0;
        }

        .panel-cross span:nth-child(2) {
            width: 14px; height: 52px;
            top: 0; left: 19px;
        }

        .panel-headline {
            font-size: 2.6rem;
            line-height: 1.15;
            color: #fff;
            position: relative;
            z-index: 1;
            margin-bottom: .75rem;
        }

        .panel-sub {
            color: rgba(255,255,255,.72);
            font-size: .9rem;
            font-weight: 300;
            position: relative;
            z-index: 1;
            line-height: 1.6;
        }

        .panel-dots {
            position: absolute;
            bottom: 2.5rem; right: 2.5rem;
            display: grid;
            grid-template-columns: repeat(5, 8px);
            gap: 8px;
            z-index: 1;
        }

        .panel-dots span {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,.25);
        }

        .panel-dots span:nth-child(3),
        .panel-dots span:nth-child(8),
        .panel-dots span:nth-child(13) {
            background: rgba(255,255,255,.65);
        }

        /* ── Panel derecho (formulario) ── */
        .auth-form-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .auth-form-inner {
            width: 100%;
            max-width: 400px;
        }

        .auth-form-inner h2 {
            font-size: 2rem;
            color: var(--teal-dark);
            margin-bottom: .25rem;
        }

        .auth-form-inner .subtitle {
            color: #6c757d;
            font-size: .875rem;
            margin-bottom: 2rem;
        }

        /* Inputs */
        .field-wrap { position: relative; margin-bottom: 1.25rem; }

        .field-wrap label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: var(--teal-dark);
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: .4rem;
        }

        .field-wrap input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1.5px solid #dee2e6;
            border-radius: .6rem;
            font-size: .9rem;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .field-wrap input:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(10,147,150,.12);
        }

        .field-wrap .toggle-pw {
            position: absolute;
            right: .85rem;
            top: 2.35rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #adb5bd;
            padding: 0;
            line-height: 1;
        }

        .field-wrap .toggle-pw:hover { color: var(--teal); }

        /* Alerta */
        .auth-alert {
            background: #fff0f0;
            border: 1px solid #fcc;
            color: #c0392b;
            border-radius: .6rem;
            padding: .65rem 1rem;
            font-size: .83rem;
            margin-bottom: 1.25rem;
            display: none;
        }

        /* Botón */
        .btn-auth {
            width: 100%;
            padding: .8rem;
            background: var(--teal);
            color: #fff;
            border: none;
            border-radius: .6rem;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: 1.5rem;
        }

        .btn-auth:hover { background: var(--teal-dark); }
        .btn-auth:active { transform: scale(.98); }
        .btn-auth:disabled { opacity: .7; cursor: not-allowed; }

        .btn-auth .spinner-border {
            width: 1rem; height: 1rem;
            border-width: 2px;
        }

        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.5rem 0;
            color: #adb5bd;
            font-size: .8rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #dee2e6;
        }

        .auth-footer {
            text-align: center;
            font-size: .85rem;
            color: #6c757d;
        }

        .auth-footer a {
            color: var(--teal);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-panel { display: none; }
            body { background: var(--cream); }
        }
    </style>
</head>
<body>

<!-- Panel izquierdo -->
<div class="auth-panel">
    <div class="panel-cross">
        <span></span><span></span>
    </div>

    <div class="panel-headline">
        Tu salud,<br>
        nuestra<br>
        prioridad.
    </div>
    <p class="panel-sub">
        Agenda citas médicas de forma rápida,<br>
        segura y desde cualquier lugar.
    </p>

    <div class="panel-dots">
        <?php for($i=0;$i<15;$i++) echo '<span></span>'; ?>
    </div>
</div>

<!-- Formulario -->
<div class="auth-form-wrap">
    <div class="auth-form-inner">

        <h2>Bienvenido</h2>
        <p class="subtitle">Ingresa tus datos para continuar</p>

        <div class="auth-alert" id="alert-error"></div>

        <form id="form-login" novalidate>

            <div class="field-wrap">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" placeholder="correo@ejemplo.com"
                       required autocomplete="email">
            </div>

            <div class="field-wrap">
                <label for="password">Contraseña</label>
                <input type="password" id="password" placeholder="••••••••"
                       required autocomplete="current-password">
                <button type="button" class="toggle-pw" id="toggle-pass" tabindex="-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                         fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg>
                </button>
            </div>

            <button type="submit" class="btn-auth" id="btn-login">
                <span id="btn-text">Iniciar sesión</span>
                <span id="btn-spinner" class="spinner-border d-none" role="status"></span>
            </button>

        </form>

        <div class="auth-divider">o</div>

        <p class="auth-footer">
            ¿No tienes cuenta?
            <a href="<?= htmlspecialchars($w) ?>/views/auth/registro.php">Regístrate gratis</a>
        </p>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($w) ?>/assets/js/auth.js"></script>
</body>
</html>