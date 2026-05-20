<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/paths.php';
$w = mc_web_base();

session_start();
if (empty($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'cliente') {
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
    <title>Mis citas · MediConnect</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons v1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --teal:       #0a9396;
            --teal-dark:  #005f73;
            --teal-light: #94d2bd;
            --cream:      #fefae0;
            --radius:     1rem;
        }

        /* ===== GLOBAL TYPOGRAPHY ===== */
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f4f7fb;
            min-height: 100vh;
            color: #212529;
            line-height: 1.5;
        }

        h1, h2, h3, h4, h5, h6,
        .btn, .form-control, .form-select,
        .badge, .nav-link, .modal-title,
        .filter-pill, .cita-card * {
            font-family: 'DM Sans', sans-serif;
        }

        /* ===== NAVBAR ===== */
        .mc-navbar {
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .mc-brand {
            font-family: 'Instrument Serif', serif;
            font-size: 1.4rem;
            color: var(--teal-dark);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .mc-brand em { color: var(--teal); font-style: normal; font-weight: 700; }

        .mc-navbar .greeting {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .mc-navbar .greeting strong {
            color: var(--teal-dark);
            font-weight: 600;
        }

        .btn-nueva {
            background: var(--teal);
            color: #fff;
            border: none;
            border-radius: 0.6rem;
            padding: 0.45rem 1.1rem;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(10,147,150,0.2);
        }
        .btn-nueva:hover {
            background: var(--teal-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(10,147,150,0.3);
        }

        .btn-salir {
            background: transparent;
            border: 1.5px solid #dee2e6;
            color: #6c757d;
            border-radius: 0.6rem;
            padding: 0.4rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .btn-salir:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: rgba(10,147,150,0.05);
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-family: 'DM Sans', sans-serif;
            font-size: 1.5rem;
            color: var(--teal-dark);
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        /* ===== FILTER PILLS ===== */
        .filter-pill {
            border: 1.5px solid #dee2e6;
            background: #fff;
            color: #6c757d;
            border-radius: 2rem;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .filter-pill:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: rgba(10,147,150,0.05);
        }
        .filter-pill.active {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff;
            box-shadow: 0 4px 12px rgba(10,147,150,0.25);
        }

        /* ===== APPOINTMENT CARDS ===== */
        .cita-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 1.25rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1.5px solid transparent;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .cita-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(10,147,150,0.15);
            border-color: var(--teal-light);
        }

        .cita-card .servicio {
            font-family: 'DM Sans', sans-serif;
            font-size: 1.15rem;
            color: var(--teal-dark);
            margin-bottom: 0.5rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .cita-card .meta {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.4rem;
        }
        .cita-card .meta i {
            font-size: 1rem;
            color: var(--teal);
            width: 1.25rem;
            text-align: center;
        }

        .cita-card .medico-info {
            font-size: 0.9rem;
            color: #495057;
            margin: 0.5rem 0;
            padding: 0.5rem 0;
            border-top: 1px dashed #e9ecef;
            border-bottom: 1px dashed #e9ecef;
        }

        .cita-card .actions {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding-top: 0.75rem;
        }

        /* ===== BADGES ===== */
        .badge-pendiente  { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
        .badge-confirmada { background:#d1e7dd; color:#0a3622; border:1px solid #198754; }
        .badge-cancelada  { background:#f8d7da; color:#58151c; border:1px solid #dc3545; }
        .badge-finalizada { background:#e2e3e5; color:#383d41; border:1px solid #adb5bd; }

        .cita-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .btn-cancelar {
            border: 1.5px solid #f8d7da;
            background: transparent;
            color: #dc3545;
            border-radius: 0.5rem;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-cancelar:hover {
            background: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .empty-state .empty-icon {
            width: 80px;
            height: 80px;
            background: rgba(10,147,150,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--teal);
        }
        .empty-state .empty-icon i {
            font-size: 2rem;
        }
        .empty-state h5 {
            font-weight: 600;
            color: var(--teal-dark);
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: #6c757d;
            margin-bottom: 1.5rem;
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

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .mc-navbar { padding: 0.75rem 1rem; flex-wrap: wrap; gap: 0.75rem; }
            .mc-navbar .greeting { order: 3; width: 100%; text-align: center; margin-top: 0.5rem; }
            .section-title { font-size: 1.3rem; }
            .filter-pill { padding: 0.35rem 0.85rem; font-size: 0.8rem; }
            .cita-card { padding: 1rem; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="mc-navbar">
    <a class="mc-brand" href="#">
        <i class="bi bi-heart-pulse-fill"></i>
        <span><em>Medi</em>Connect</span>
    </a>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="greeting d-none d-sm-inline">
            Hola, <strong><?= htmlspecialchars($_SESSION['user_nombre']) ?></strong>
        </span>
        <a href="<?= htmlspecialchars($w) ?>/views/cliente/agendar.php" class="btn-nueva">
            <i class="bi bi-plus-lg"></i> Nueva cita
        </a>
        <button class="btn-salir" id="btn-logout">
            <i class="bi bi-box-arrow-right"></i> Salir
        </button>
    </div>
</nav>

<!-- Toast & Spinner -->
<div id="toast-box" role="region" aria-live="polite"></div>
<div id="spinner-overlay" aria-busy="true">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<!-- Main Content -->
<div class="container py-4 pb-5">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2">
        <h1 class="section-title mb-0">Mis citas</h1>
    </div>
    <p class="text-muted small mb-4">Gestiona y consulta el historial de tus citas médicas.</p>

    <!-- Filters -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <button class="filter-pill" data-filter="todas">
            <i class="bi bi-grid-3x3"></i> Todas
        </button>
        <button class="filter-pill active" data-filter="pendiente">
            <i class="bi bi-hourglass-split"></i> Pendientes
        </button>
        <button class="filter-pill" data-filter="confirmada">
            <i class="bi bi-check-circle"></i> Confirmadas
        </button>
        <button class="filter-pill" data-filter="finalizada">
            <i class="bi bi-check2-all"></i> Finalizadas
        </button>
        <button class="filter-pill" data-filter="cancelada">
            <i class="bi bi-x-circle"></i> Canceladas
        </button>
    </div>

    <!-- Appointments List (JS renders here) -->
    <div id="lista-citas" class="row g-3"></div>

    <!-- Empty State -->
    <div id="empty-state" class="empty-state d-none">
        <div class="empty-icon">
            <i class="bi bi-calendar-x"></i>
        </div>
        <h5>Sin citas</h5>
        <p class="mb-3">No tienes citas en esta categoría.</p>
        <a href="<?= htmlspecialchars($w) ?>/views/cliente/agendar.php" class="btn-nueva">
            <i class="bi bi-calendar-plus"></i> Agendar cita
        </a>
    </div>

</div>

<!-- Modal: Cancel Appointment -->
<div class="modal fade" id="modal-cancelar" tabindex="-1" aria-labelledby="modal-cancelar-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title" id="modal-cancelar-label">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> ¿Cancelar esta cita?
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-0" id="modal-cancelar-info"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> No, mantener
                </button>
                <button class="btn btn-danger btn-sm" id="btn-confirmar-cancelar">
                    <i class="bi bi-trash-fill"></i> Sí, cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($w) ?>/assets/js/dashboard-cliente.js?v=<?= time() ?>"></script>
</body>
</html>