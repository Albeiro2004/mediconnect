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
    <title>Médicos · MediConnect</title>

    <!-- Google Fonts: DM Sans (global) + Instrument Serif (solo marca) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons v1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="<?= htmlspecialchars($w) ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($w) ?>/assets/css/admin.css">

    <style>
        :root {
            --teal:       #0a9396;
            --teal-dark:  #005f73;
            --teal-light: #94d2bd;
            --sidebar-width: 260px;
        }

        /* ===== GLOBAL TYPOGRAPHY ===== */
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            color: #212529;
            line-height: 1.5;
        }

        h1, h2, h3, h4, h5, h6,
        .btn, .form-control, .form-select,
        .table, .badge, .nav-link, .modal-title,
        .form-label, .alert {
            font-family: 'DM Sans', sans-serif;
        }

        /* ===== SIDEBAR ===== */
        .mc-sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--teal-dark), var(--teal));
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 1rem;
            z-index: 1040;
            transition: transform 0.3s ease;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }

        .mc-sidebar-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0.75rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 1rem;
        }

        .mc-sidebar-brand .fw-bold {
            font-family: 'Instrument Serif', serif;
            font-size: 1.4rem;
        }

        .mc-sidebar-nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 0 0.25rem;
        }

        .mc-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            border-radius: 0.6rem;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .mc-nav-link:hover,
        .mc-nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
            transform: translateX(4px);
        }

        .mc-nav-link i {
            font-size: 1.1rem;
            width: 1.25rem;
            text-align: center;
        }

        .mc-sidebar-footer {
            padding: 1rem 0.75rem;
            border-top: 1px solid rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mc-sidebar-footer .small {
            color: rgba(255,255,255,0.9);
            font-weight: 500;
        }

        /* ===== MAIN & TOPBAR ===== */
        .mc-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .mc-topbar {
            position: sticky;
            top: 0;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1030;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }

        .mc-topbar h6 {
            font-weight: 600;
            color: var(--teal-dark);
            font-size: 1.25rem;
            flex: 1;
        }

        /* ===== PRIMARY BUTTON ===== */
        .btn-mc {
            background: var(--teal);
            border-color: var(--teal);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-mc:hover {
            background: var(--teal-dark);
            border-color: var(--teal-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(10,147,150,0.3);
        }

        /* ===== FILTER SELECT ===== */
        #filtro-sede {
            border-radius: 0.6rem;
            border: 1px solid #ced4da;
            font-size: 0.9rem;
            padding: 0.35rem 0.75rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        #filtro-sede:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 0.2rem rgba(10,147,150,0.15);
        }

        /* ===== TABLE CARD ===== */
        .mc-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            background: #fff;
        }

        .table thead th {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6c757d;
            border-bottom: 2px solid #f0f0f0;
            padding: 0.85rem 1rem;
            font-weight: 600;
            background: #fafafa;
        }

        .table tbody td {
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
            vertical-align: middle;
            border-color: #f0f0f0;
        }

        .table tbody tr:hover {
            background: rgba(10,147,150,0.03);
        }

        /* ===== ACTION BUTTONS ===== */
        .btn-action {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-action.edit {
            background: rgba(13,110,253,0.1);
            color: #0d6efd;
            border-color: rgba(13,110,253,0.2);
        }
        .btn-action.edit:hover {
            background: #0d6efd;
            color: #fff;
        }
        .btn-action.delete {
            background: rgba(220,53,69,0.1);
            color: #dc3545;
            border-color: rgba(220,53,69,0.2);
        }
        .btn-action.delete:hover {
            background: #dc3545;
            color: #fff;
        }

        /* ===== FORM INPUTS ===== */
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.4rem;
        }
        .form-control, .form-select {
            border-radius: 0.6rem;
            border: 1px solid #ced4da;
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 0.2rem rgba(10,147,150,0.15);
        }
        .form-control::placeholder {
            color: #adb5bd;
            font-style: italic;
        }
        textarea.form-control { resize: vertical; min-height: 80px; }

        /* ===== ALERT ===== */
        .alert {
            border-radius: 0.6rem;
            border: 1px solid transparent;
            font-weight: 500;
        }
        .alert-danger {
            background: rgba(220,53,69,0.1);
            color: #58151c;
            border-color: rgba(220,53,69,0.3);
        }

        /* ===== MODAL ===== */
        .modal-content {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        .modal-header {
            border-bottom: 1px solid #f0f0f0;
            padding: 1.25rem 1.5rem;
        }
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--teal-dark);
        }
        .modal-body { padding: 1.25rem 1.5rem; }
        .modal-footer {
            border-top: 1px solid #f0f0f0;
            padding: 1rem 1.5rem;
            gap: 0.5rem;
        }

        /* ===== TOAST & SPINNER ===== */
        #toast-box {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 300px;
        }

        #spinner-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9998;
        }
        #spinner-overlay.show { display: flex; }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.25em;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .mc-sidebar { transform: translateX(-100%); }
            .mc-sidebar.show { transform: translateX(0); }
            .mc-main { margin-left: 0; }
            .mc-main.sidebar-open { margin-left: var(--sidebar-width); }
            .mc-topbar { padding: 0.75rem 1rem; }
            .btn-mc { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
            .d-flex.align-items-center.gap-2 { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="mc-sidebar" id="sidebar" aria-label="Menú principal">
    <div class="mc-sidebar-brand">
        <span class="fw-bold"><span class="text-info">Medi</span>Connect</span>
        <button class="btn btn-sm text-white d-md-none" id="btn-close-sidebar" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <nav class="mc-sidebar-nav">
        <a href="<?= htmlspecialchars($w) ?>/views/admin/dashboard.php" class="mc-nav-link">
            <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/admin/sedes.php" class="mc-nav-link">
            <i class="bi bi-hospital"></i> <span>Sedes</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/admin/medicos.php" class="mc-nav-link active">
            <i class="bi bi-person-gear"></i> <span>Médicos</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/admin/servicios.php" class="mc-nav-link">
            <i class="bi bi-heart-pulse"></i> <span>Servicios</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/admin/citas.php" class="mc-nav-link">
            <i class="bi bi-calendar-check"></i> <span>Citas</span>
        </a>
    </nav>
    <div class="mc-sidebar-footer">
        <span class="small text-truncate"><?= htmlspecialchars($_SESSION['user_nombre']) ?></span>
        <button class="btn btn-sm btn-outline-light ms-2 d-flex align-items-center gap-1" id="btn-logout">
            <i class="bi bi-box-arrow-right"></i> Salir
        </button>
    </div>
</aside>

<!-- Main Content -->
<main class="mc-main" id="main-content">

    <!-- Topbar -->
    <header class="mc-topbar">
        <button class="btn btn-sm btn-outline-secondary d-md-none" id="btn-open-sidebar" aria-label="Abrir menú">
            <i class="bi bi-list"></i>
        </button>
        <h6 class="mb-0">Médicos</h6>
        <button class="btn btn-sm btn-mc text-white" id="btn-nuevo-medico">
            <i class="bi bi-person-plus"></i> Nuevo médico
        </button>
    </header>

    <!-- Toast & Spinner -->
    <div id="toast-box" role="region" aria-live="polite"></div>
    <div id="spinner-overlay" aria-busy="true">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Content -->
    <div class="p-4">

        <!-- Filter by Sede -->
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <label class="small fw-semibold mb-0" for="filtro-sede">
                <i class="bi bi-funnel me-1"></i> Filtrar por sede:
            </label>
            <select class="form-select form-select-sm" id="filtro-sede" style="max-width:220px">
                <option value="">Todas las sedes</option>
            </select>
        </div>

        <!-- Table -->
        <div class="card mc-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Email</th>
                        <th scope="col">Especialidad</th>
                        <th scope="col">Sede</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tabla-medicos">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <i class="bi bi-hourglass-split fs-4"></i>
                                <span>Cargando médicos...</span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal: Create / Edit Médico -->
<!-- IDs críticos preservados: #modal-medico, #modal-medico-titulo, #modal-alert, #medico-*, #campos-creacion -->
<div class="modal fade" id="modal-medico" tabindex="-1" aria-labelledby="modal-medico-titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title" id="modal-medico-titulo">Nuevo médico</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Alert: estructura simple para compatibilidad con .textContent del JS -->
                <div id="modal-alert" class="alert alert-danger py-2 small d-none"></div>

                <input type="hidden" id="medico-id">

                <!-- Campos solo para creación -->
                <div id="campos-creacion">
                    <div class="mb-3">
                        <label for="medico-nombre" class="form-label small">Nombre completo</label>
                        <input type="text" class="form-control" id="medico-nombre" placeholder="Ej: Ana García" required>
                    </div>
                    <div class="mb-3">
                        <label for="medico-email" class="form-label small">Email</label>
                        <input type="email" class="form-control" id="medico-email" placeholder="medico@ejemplo.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="medico-password" class="form-label small">Contraseña</label>
                        <input type="password" class="form-control" id="medico-password" placeholder="Mínimo 8 caracteres" minlength="8">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="medico-sede" class="form-label small">Sede</label>
                    <select class="form-select" id="medico-sede" required>
                        <option value="">Cargando sedes...</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="medico-especialidad" class="form-label small">Especialidad</label>
                    <select class="form-select" id="medico-especialidad" required>
                        <option value="">Selecciona una especialidad</option>
                        <option value="Medicina General">Medicina General</option>
                        <option value="Pediatría">Pediatría</option>
                        <option value="Ginecología y Obstetricia">Ginecología y Obstetricia</option>
                        <option value="Cardiología">Cardiología</option>
                        <option value="Dermatología">Dermatología</option>
                        <option value="Ortopedia y Traumatología">Ortopedia y Traumatología</option>
                        <option value="Neurología">Neurología</option>
                        <option value="Psiquiatría">Psiquiatría</option>
                        <option value="Oftalmología">Oftalmología</option>
                        <option value="Otorrinolaringología">Otorrinolaringología</option>
                        <option value="Urología">Urología</option>
                        <option value="Endocrinología">Endocrinología</option>
                        <option value="Gastroenterología">Gastroenterología</option>
                        <option value="Neumología">Neumología</option>
                        <option value="Odontología">Odontología</option>
                    </select>
                </div>

                <div class="mb-1">
                    <label for="medico-perfil" class="form-label small">Perfil profesional <span class="text-muted">(opcional)</span></label>
                    <textarea class="form-control" id="medico-perfil" rows="2"
                              placeholder="Breve descripción del médico"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Cancelar
                </button>
                <button class="btn btn-sm btn-mc text-white" id="btn-guardar-medico">
                    <span id="btn-medico-txt">Guardar</span>
                    <span class="spinner-border spinner-border-sm ms-1 d-none" id="btn-medico-spin"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Delete Confirmation -->
<!-- IDs críticos preservados: #modal-eliminar, #modal-eliminar-info, #btn-confirmar-eliminar -->
<div class="modal fade" id="modal-eliminar" tabindex="-1" aria-labelledby="modal-eliminar-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-danger" id="modal-eliminar-label">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> ¿Eliminar médico?
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-0" id="modal-eliminar-info"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Cancelar
                </button>
                <button class="btn btn-danger btn-sm" id="btn-confirmar-eliminar">
                    <i class="bi bi-trash-fill"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($w) ?>/assets/js/admin-medicos.js"></script>
</body>
</html>