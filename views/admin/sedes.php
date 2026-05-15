<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/paths.php';
$w = mc_web_base();

session_start();
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], ['superadmin', 'admin_sede'])) {
    header('Location: ' . $w . '/views/auth/login.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sedes · MediConnect</title>
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
        <a href="<?= htmlspecialchars($w) ?>/views/admin/dashboard.php" class="mc-nav-link">📊 <span>Dashboard</span></a>
        <a href="<?= htmlspecialchars($w) ?>/views/admin/sedes.php"     class="mc-nav-link active">🏥 <span>Sedes</span></a>
        <a href="<?= htmlspecialchars($w) ?>/views/admin/medicos.php"   class="mc-nav-link">👨‍⚕️ <span>Médicos</span></a>
        <a href="<?= htmlspecialchars($w) ?>/views/admin/servicios.php" class="mc-nav-link">🩺 <span>Servicios</span></a>
        <a href="<?= htmlspecialchars($w) ?>/views/admin/citas.php"     class="mc-nav-link">📋 <span>Citas</span></a>
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
        <h6 class="mb-0 fw-bold">Sedes</h6>
        <button class="btn btn-sm btn-mc btn-primary" id="btn-nueva-sede">+ Nueva sede</button>
    </div>

    <div id="toast-box"></div>
    <div id="spinner-overlay"><div class="spinner-border text-primary"></div></div>

    <div class="p-4">
        <div class="card mc-card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Ciudad</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="tabla-sedes">
                    <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal crear / editar -->
<div class="modal fade" id="modal-sede" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold" id="modal-sede-titulo">Nueva sede</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-alert" class="alert alert-danger py-2 small d-none"></div>
                <input type="hidden" id="sede-id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nombre</label>
                    <input type="text" class="form-control" id="sede-nombre" placeholder="Ej: Clínica Norte">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Ciudad</label>
                    <input type="text" class="form-control" id="sede-ciudad" placeholder="Ej: Montería">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Dirección</label>
                    <input type="text" class="form-control" id="sede-direccion" placeholder="Ej: Cra 5 #10-20">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Teléfono <span class="text-muted">(opcional)</span></label>
                    <input type="text" class="form-control" id="sede-telefono" placeholder="Ej: 3001234567">
                </div>
                <div class="mb-1 d-none" id="campo-estado">
                    <label class="form-label small fw-semibold">Estado</label>
                    <select class="form-select" id="sede-estado">
                        <option value="activa">Activa</option>
                        <option value="inactiva">Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm btn-mc" id="btn-guardar-sede">
                    <span id="btn-sede-txt">Guardar</span>
                    <span class="spinner-border spinner-border-sm ms-1 d-none" id="btn-sede-spin"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal eliminar -->
<div class="modal fade" id="modal-eliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">¿Eliminar sede?</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-muted small" id="modal-eliminar-info"></div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm" id="btn-confirmar-eliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($w) ?>/assets/js/admin-sedes.js"></script>
</body>
</html>