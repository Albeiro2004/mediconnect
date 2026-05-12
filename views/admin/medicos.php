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
    <title>Médicos · MediConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/main.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/admin.css">
</head>
<body>

<!-- Sidebar -->
<div class="mc-sidebar" id="sidebar">
    <div class="mc-sidebar-brand">
        <span class="text-white fw-bold fs-5"><span class="text-info">Medi</span>Connect</span>
        <button class="btn btn-sm text-white d-md-none" id="btn-close-sidebar">✕</button>
    </div>
    <nav class="mc-sidebar-nav">
        <a href="/mediconnect/views/admin/dashboard.php" class="mc-nav-link">📊 <span>Dashboard</span></a>
        <a href="/mediconnect/views/admin/sedes.php"     class="mc-nav-link">🏥 <span>Sedes</span></a>
        <a href="/mediconnect/views/admin/medicos.php"   class="mc-nav-link active">👨‍⚕️ <span>Médicos</span></a>
        <a href="/mediconnect/views/admin/servicios.php" class="mc-nav-link">🩺 <span>Servicios</span></a>
        <a href="/mediconnect/views/admin/citas.php"     class="mc-nav-link">📋 <span>Citas</span></a>
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
        <h6 class="mb-0 fw-bold">Médicos</h6>
        <button class="btn btn-sm btn-primary btn-mc" id="btn-nuevo-medico">+ Nuevo médico</button>
    </div>

    <div id="toast-box"></div>
    <div id="spinner-overlay"><div class="spinner-border text-primary"></div></div>

    <div class="p-4">

        <div class="d-flex align-items-center gap-2 mb-3">
            <label class="small fw-semibold mb-0">Filtrar por sede:</label>
            <select class="form-select form-select-sm" id="filtro-sede" style="max-width:220px">
                <option value="">Todas las sedes</option>
            </select>
        </div>

        <div class="card mc-card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Especialidad</th>
                        <th>Sede</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="tabla-medicos">
                    <tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal crear / editar -->
<div class="modal fade" id="modal-medico" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold" id="modal-medico-titulo">Nuevo médico</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-alert" class="alert alert-danger py-2 small d-none"></div>
                <input type="hidden" id="medico-id">

                <div id="campos-creacion">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nombre completo</label>
                        <input type="text" class="form-control" id="medico-nombre" placeholder="Ej: Ana García">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" class="form-control" id="medico-email" placeholder="medico@ejemplo.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Contraseña</label>
                        <input type="password" class="form-control" id="medico-password" placeholder="Mínimo 8 caracteres">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Sede</label>
                    <select class="form-select" id="medico-sede"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Especialidad</label>
                    <input type="text" class="form-control" id="medico-especialidad" placeholder="Ej: Medicina General">
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Perfil profesional <span class="text-muted">(opcional)</span></label>
                    <textarea class="form-control" id="medico-perfil" rows="2"
                              placeholder="Breve descripción del médico"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm btn-mc" id="btn-guardar-medico">
                    <span id="btn-medico-txt">Guardar</span>
                    <span class="spinner-border spinner-border-sm ms-1 d-none" id="btn-medico-spin"></span>
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
                <h6 class="modal-title fw-bold">¿Eliminar médico?</h6>
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
<script src="/mediconnect/assets/js/admin-medicos.js"></script>
</body>
</html>