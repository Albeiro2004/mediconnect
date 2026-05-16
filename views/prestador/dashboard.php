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
    <title>Mi Panel · MediConnect</title>
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
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/dashboard.php"       class="mc-nav-link active">
            📊 <span>Mi agenda</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/disponibilidad.php"  class="mc-nav-link">
            🗓 <span>Disponibilidad</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/logs.php"           class="mc-nav-link">
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
        <h6 class="mb-0 fw-bold">Mi agenda</h6>
        <span class="badge bg-info text-dark">Prestador</span>
    </div>

    <div id="toast-box"></div>
    <div id="spinner-overlay"><div class="spinner-border text-primary"></div></div>

    <div class="p-4">

        <!-- Filtro por fecha -->
        <div class="d-flex align-items-center gap-2 mb-3">
            <label class="small fw-semibold mb-0">Fecha:</label>
            <label for="filtro-fecha"></label><input type="date" id="filtro-fecha" class="form-control form-control-sm" style="max-width:180px">
            <button class="btn btn-sm btn-outline-secondary" id="btn-limpiar-fecha">Ver todas</button>
        </div>

        <!-- Stats rápidas -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card mc-card p-3 stat-card">
                    <div class="stat-icon bg-primary-soft">📋</div>
                    <div class="stat-value" id="stat-total">—</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mc-card p-3 stat-card">
                    <div class="stat-icon bg-warning-soft">⏳</div>
                    <div class="stat-value text-warning" id="stat-pendientes">—</div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mc-card p-3 stat-card">
                    <div class="stat-icon bg-success-soft">✅</div>
                    <div class="stat-value text-success" id="stat-confirmadas">—</div>
                    <div class="stat-label">Confirmadas</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card mc-card p-3 stat-card">
                    <div class="stat-icon bg-danger-soft">🏁</div>
                    <div class="stat-value text-secondary" id="stat-finalizadas">—</div>
                    <div class="stat-label">Finalizadas</div>
                </div>
            </div>
        </div>

        <!-- Tabla de citas -->
        <div class="card mc-card p-3">
            <h6 class="fw-bold mb-3">Citas asignadas</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="tabla-citas">
                    <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal cambiar estado -->
<div class="modal fade" id="modal-estado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Cambiar estado</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3" id="modal-estado-info"></p>
                <label for="select-estado"></label><select class="form-select" id="select-estado">
                    <option value="confirmada">Confirmada</option>
                    <option value="finalizada">Finalizada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm btn-mc" id="btn-guardar-estado">
                    <span id="btn-estado-txt">Guardar</span>
                    <span class="spinner-border spinner-border-sm ms-1 d-none" id="btn-estado-spin"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($w) ?>/assets/js/prestador-dashboard.js"></script>
</body>
</html>