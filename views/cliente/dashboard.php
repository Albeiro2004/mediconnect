<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/paths.php';
$w = mc_web_base();

session_start();
if (empty($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'cliente') {
    header('Location: ' . $w . '/views/auth/login.php');
    exit;
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis citas · MediConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($w) ?>/assets/css/main.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar mc-navbar px-3 mb-4">
        <a class="navbar-brand" href="#">
            <strong>Medi</strong>Connect
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small d-none d-sm-inline">
                Hola, <strong><?= htmlspecialchars($_SESSION['user_nombre']) ?></strong>
            </span>
            <a href="<?= htmlspecialchars($w) ?>/views/cliente/agendar.php" class="btn btn-primary btn-sm btn-mc">
                + Nueva cita
            </a>
            <button class="btn btn-outline-secondary btn-sm" id="btn-logout">Salir</button>
        </div>
    </nav>

    <!-- Toast container -->
    <div id="toast-box"></div>

    <!-- Spinner -->
    <div id="spinner-overlay">
        <div class="spinner-border text-primary"></div>
    </div>

    <!-- Contenido -->
    <div class="container pb-5">

        <h5 class="fw-bold mb-4">📋 Mis citas</h5>

        <!-- Filtros de estado -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <button class="btn btn-sm btn-outline-secondary active" data-filter="todas">Todas</button>
            <button class="btn btn-sm btn-outline-warning" data-filter="pendiente">Pendientes</button>
            <button class="btn btn-sm btn-outline-success" data-filter="confirmada">Confirmadas</button>
            <button class="btn btn-sm btn-outline-secondary" data-filter="finalizada">Finalizadas</button>
            <button class="btn btn-sm btn-outline-danger" data-filter="cancelada">Canceladas</button>
        </div>

        <!-- Lista de citas -->
        <div id="lista-citas" class="row g-3">
            <!-- Se renderizan por JS -->
        </div>

        <!-- Estado vacío -->
        <div id="empty-state" class="text-center py-5 d-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="#adb5bd" viewBox="0 0 16 16" class="mb-3">
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
            </svg>
            <p class="text-muted">No tienes citas en esta categoría.</p>
            <a href="<?= htmlspecialchars($w) ?>/views/cliente/agendar.php" class="btn btn-primary btn-mc btn-sm">Agendar cita</a>
        </div>

    </div>

    <!-- Modal confirmar cancelación -->
    <div class="modal fade" id="modal-cancelar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold">¿Cancelar esta cita?</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-muted small" id="modal-cancelar-info"></div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">No, mantener</button>
                    <button class="btn btn-danger btn-sm" id="btn-confirmar-cancelar">Sí, cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($w) ?>/assets/js/dashboard-cliente.js"></script>
</body>

</html>