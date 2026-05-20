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
        .form-label, .alert, .stat-card * {
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

        /* ===== BADGE ROL ===== */
        .badge-prestador {
            background: linear-gradient(135deg, var(--teal-light), var(--teal));
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 0.4rem 0.85rem;
            border-radius: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            box-shadow: 0 2px 8px rgba(10,147,150,0.25);
        }

        /* ===== FILTER DATE ===== */
        #filtro-fecha {
            border-radius: 0.6rem;
            border: 1px solid #ced4da;
            font-size: 0.9rem;
            padding: 0.35rem 0.75rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        #filtro-fecha:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 0.2rem rgba(10,147,150,0.15);
        }

        .btn-limpiar {
            border-radius: 0.6rem;
            font-weight: 500;
            font-size: 0.85rem;
            padding: 0.35rem 0.85rem;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 1.25rem !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: #fff;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .stat-icon {
            font-size: 1.5rem;
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        .stat-icon i { font-size: 1.25rem; }

        .stat-value {
            font-size: 1.75rem;
            line-height: 1;
            margin-bottom: 0.25rem;
            font-weight: 700;
            color: var(--teal-dark);
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .bg-primary-soft { background: rgba(13,110,253,0.12); color: #0d6efd; }
        .bg-warning-soft { background: rgba(255,193,7,0.15); color: #856404; }
        .bg-success-soft { background: rgba(25,135,84,0.12); color: #0a3622; }
        .bg-danger-soft  { background: rgba(220,53,69,0.12); color: #58151c; }

        /* ===== TABLE CARD ===== */
        .mc-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            background: #fff;
        }

        .mc-card h6.fw-bold {
            font-size: 1.1rem;
            color: var(--teal-dark);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .mc-card h6.fw-bold i { color: var(--teal); }

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

        /* ===== STATUS BADGES (Crítico: usados por badgeEstado() en JS) ===== */
        .badge-pendiente  { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
        .badge-confirmada { background:#d1e7dd; color:#0a3622; border:1px solid #198754; }
        .badge-cancelada  { background:#f8d7da; color:#58151c; border:1px solid #dc3545; }
        .badge-finalizada { background:#e2e3e5; color:#383d41; border:1px solid #adb5bd; }

        .badge-pendiente, .badge-confirmada, .badge-cancelada, .badge-finalizada {
            border-radius: 2rem;
            padding: 0.4rem 0.85rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
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

        .form-select {
            border-radius: 0.6rem;
            border: 1px solid #ced4da;
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 0.2rem rgba(10,147,150,0.15);
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
            .stat-value { font-size: 1.5rem; }
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
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/dashboard.php" class="mc-nav-link active">
            <i class="bi bi-speedometer2"></i> <span>Mi agenda</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/disponibilidad.php" class="mc-nav-link">
            <i class="bi bi-calendar-week"></i> <span>Disponibilidad</span>
        </a>
        <a href="<?= htmlspecialchars($w) ?>/views/prestador/logs.php" class="mc-nav-link">
            <i class="bi bi-journal-text"></i> <span>Logs de atención</span>
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
        <h6 class="mb-0">Mi agenda</h6>
        <span class="badge badge-prestador">
            <i class="bi bi-person-check"></i> Prestador
        </span>
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

        <!-- Date Filter -->
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <label class="small fw-semibold mb-0" for="filtro-fecha">
                <i class="bi bi-funnel me-1"></i> Fecha:
            </label>
            <input type="date" id="filtro-fecha" class="form-control form-control-sm" style="max-width:180px">
            <button class="btn btn-sm btn-outline-secondary btn-limpiar" id="btn-limpiar-fecha">
                <i class="bi bi-x-circle"></i> Ver todas
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card stat-card">
                    <div class="stat-icon bg-primary-soft">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="stat-value" id="stat-total">—</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card">
                    <div class="stat-icon bg-warning-soft">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-value text-warning" id="stat-pendientes">—</div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card">
                    <div class="stat-icon bg-success-soft">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stat-value text-success" id="stat-confirmadas">—</div>
                    <div class="stat-label">Confirmadas</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card">
                    <div class="stat-icon bg-danger-soft">
                        <i class="bi bi-flag-fill"></i>
                    </div>
                    <div class="stat-value text-secondary" id="stat-finalizadas">—</div>
                    <div class="stat-label">Finalizadas</div>
                </div>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="card mc-card p-3">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-list-ul"></i> Citas asignadas
            </h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Servicio</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Hora</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tabla-citas">
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <i class="bi bi-hourglass-split fs-4"></i>
                                <span>Cargando citas...</span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Modal: Change Status -->
<!-- IDs críticos preservados: #modal-estado, #modal-estado-info, #select-estado, #btn-guardar-estado, #btn-estado-txt, #btn-estado-spin -->
<div class="modal fade" id="modal-estado" tabindex="-1" aria-labelledby="modal-estado-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title" id="modal-estado-label">Cambiar estado</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Info: estructura simple para compatibilidad con .textContent del JS -->
                <p class="small text-muted mb-3" id="modal-estado-info"></p>

                <label for="select-estado" class="form-label small fw-medium">Nuevo estado</label>
                <select class="form-select" id="select-estado">
                    <option value="confirmada">🟢 Confirmada</option>
                    <option value="finalizada">⚪ Finalizada</option>
                    <option value="cancelada">🔴 Cancelada</option>
                </select>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Cancelar
                </button>
                <button class="btn btn-sm btn-mc text-white" id="btn-guardar-estado">
                    <span id="btn-estado-txt">Guardar</span>
                    <span class="spinner-border spinner-border-sm ms-1 d-none" id="btn-estado-spin"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($w) ?>/assets/js/prestador-dashboard.js"></script>
</body>
</html>