<?php
session_start();
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], ['superadmin', 'admin_sede'])) {
    header('Location: /mediconnect/views/auth/login.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin · MediConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/main.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/admin.css">
</head>
<body>

<!-- ── Sidebar ─────────────────────────────────────────── -->
<div class="mc-sidebar" id="sidebar">
    <div class="mc-sidebar-brand">
        <span class="text-white fw-bold fs-5"><span class="text-info">Medi</span>Connect</span>
        <button class="btn btn-sm text-white d-md-none" id="btn-close-sidebar">✕</button>
    </div>

    <nav class="mc-sidebar-nav">
        <a href="/mediconnect/views/admin/dashboard.php" class="mc-nav-link active">
            📊 <span>Dashboard</span>
        </a>
        <a href="/mediconnect/views/admin/sedes.php" class="mc-nav-link">
            🏥 <span>Sedes</span>
        </a>
        <a href="/mediconnect/views/admin/medicos.php" class="mc-nav-link">
            👨‍⚕️ <span>Médicos</span>
        </a>
        <a href="/mediconnect/views/admin/servicios.php" class="mc-nav-link">
            🩺 <span>Servicios</span>
        </a>
        <a href="/mediconnect/views/admin/citas.php" class="mc-nav-link">
            📋 <span>Citas</span>
        </a>
    </nav>

    <div class="mc-sidebar-footer">
        <span class="small text-truncate"><?= htmlspecialchars($_SESSION['user_nombre']) ?></span>
        <button class="btn btn-sm btn-outline-light ms-2" id="btn-logout">Salir</button>
    </div>
</div>

<!-- ── Contenido principal ────────────────────────────── -->
<div class="mc-main" id="main-content">

    <!-- Topbar -->
    <div class="mc-topbar">
        <button class="btn btn-sm btn-outline-secondary d-md-none" id="btn-open-sidebar">☰</button>
        <h6 class="mb-0 fw-bold">Dashboard</h6>
        <span class="badge bg-primary">
            <?= $_SESSION['user_rol'] === 'superadmin' ? 'Super Admin' : 'Admin Sede' ?>
        </span>
    </div>

    <!-- Toast y spinner -->
    <div id="toast-box"></div>
    <div id="spinner-overlay"><div class="spinner-border text-primary"></div></div>

    <div class="p-4">

        <!-- Tarjetas de resumen -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card mc-card p-3 stat-card">
                    <div class="stat-icon bg-primary-soft">📋</div>
                    <div class="stat-value" id="stat-total">—</div>
                    <div class="stat-label">Total citas</div>
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
                    <div class="stat-icon bg-danger-soft">❌</div>
                    <div class="stat-value text-danger" id="stat-canceladas">—</div>
                    <div class="stat-label">Canceladas</div>
                </div>
            </div>
        </div>

        <!-- Tabla de citas recientes -->
        <div class="card mc-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Citas recientes</h6>
                <a href="/mediconnect/views/admin/citas.php" class="btn btn-sm btn-outline-primary">
                    Ver todas
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Médico</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="tabla-citas">
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Cargando...</td>
                    </tr>
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
                <h6 class="modal-title fw-bold">Cambiar estado de cita</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3" id="modal-estado-info"></p>
                <select class="form-select" id="select-estado">
                    <option value="pendiente">Pendiente</option>
                    <option value="confirmada">Confirmada</option>
                    <option value="cancelada">Cancelada</option>
                    <option value="finalizada">Finalizada</option>
                </select>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm btn-mc" id="btn-guardar-estado">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/mediconnect/assets/js/admin-dashboard.js"></script>
</body>
</html>