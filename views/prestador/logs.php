<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/paths.php';
$w = mc_web_base();

session_start();
if (empty($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'prestador') {
    header('Location: ' . $w . '/views/auth/login.php'); exit;
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
    <title>Logs de atención · MediConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($w) ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($w) ?>/assets/css/admin.css">
</head>
<body>

<!-- Sidebar -->
<div class="mc-sidebar" id="sidebar">
    <div class="mc-sidebar-brand">
        <span class="text-white fw-bold fs-5"><span class="text-info">Medi</span>Connect</span>
        <button class="btn btn-sm text-white d-md-none" id="btn-close-sidebar">✕</button>
    </div>
    <nav class="mc-sidebar-nav">
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/dashboard.php"      class="mc-nav-link">
            📊 <span>Mi agenda</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/disponibilidad.php" class="mc-nav-link">
            🗓 <span>Disponibilidad</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/logs.php"           class="mc-nav-link active">
            📝 <span>Logs de atención</span>
        </a>
    </nav>
    <div class="mc-sidebar-footer">
        <span class="small text-truncate"><?= htmlspecialchars($_SESSION['user_nombre']) ?></span>
        <button class="btn btn-sm btn-outline-light ms-2" id="btn-logout">Salir</button>
    </div>
</div>

<!-- Main -->
<div class="mc-main" id="main-content">

    <div class="mc-topbar">
        <button class="btn btn-sm btn-outline-secondary d-md-none" id="btn-open-sidebar">☰</button>
        <h6 class="mb-0 fw-bold">Logs de atención</h6>
    </div>

    <div id="toast-box"></div>
    <div id="spinner-overlay"><div class="spinner-border text-primary"></div></div>

    <div class="p-4">

        <!-- Citas finalizadas sin log -->
        <div class="card mc-card p-3 mb-4">
            <h6 class="fw-bold mb-3">⏳ Citas finalizadas pendientes de log</h6>
            <div id="lista-pendientes">
                <p class="text-muted small">Cargando...</p>
            </div>
        </div>

        <!-- Logs registrados -->
        <div class="card mc-card p-3">
            <h6 class="fw-bold mb-3">📋 Logs registrados</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Fecha cita</th>
                        <th>Próxima cita</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="tabla-logs">
                    <tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal crear / editar log -->
<div class="modal fade" id="modal-log" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold" id="modal-log-titulo">Registrar log de atención</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-alert" class="alert alert-danger py-2 small d-none"></div>
                <input type="hidden" id="log-id">
                <input type="hidden" id="log-cita-id">

                <p class="small text-muted mb-3" id="log-cita-info"></p>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Observaciones finales</label>
                    <textarea class="form-control" id="log-observaciones" rows="3"
                              placeholder="Describe el estado del paciente y hallazgos relevantes"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tratamiento o resultado</label>
                    <textarea class="form-control" id="log-tratamiento" rows="3"
                              placeholder="Describe el tratamiento indicado o el resultado de la consulta"></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Próxima cita sugerida <span class="text-muted">(opcional)</span></label>
                    <input type="date" class="form-control" id="log-proxima" style="max-width:200px">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm btn-mc" id="btn-guardar-log">
                    <span id="btn-log-txt">Guardar</span>
                    <span class="spinner-border spinner-border-sm ms-1 d-none" id="btn-log-spin"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($w) ?>/assets/js/prestador-logs.js"></script>
</body>
</html>