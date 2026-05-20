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
    <title>Agendar cita · MediConnect</title>

    <!-- Google Fonts: DM Sans (global) + Instrument Serif (solo marca) -->
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
        .step-title, .sel-card *, .slot-btn,
        .resumen-row, .btn-back, .nav-label {
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

        .nav-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .nav-label i { color: var(--teal); }

        /* ===== BACK BUTTON ===== */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--teal);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            color: var(--teal-dark);
            gap: 0.5rem;
        }
        .btn-back i { font-size: 1.1rem; }

        /* ===== STEPS BAR ===== */
        .step-bar {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            margin-bottom: 2.5rem;
            padding: 0 1rem;
            gap: 0.5rem;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            max-width: 100px;
            position: relative;
            font-size: 0.75rem;
            color: #adb5bd;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 16px;
            left: calc(50% + 18px);
            right: calc(-50% + 18px);
            height: 2px;
            background: #dee2e6;
            transition: background 0.2s ease;
            z-index: 0;
        }

        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            background: #fff;
            position: relative;
            z-index: 1;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
            color: #6c757d;
        }

        .step-item.active { color: var(--teal); }
        .step-item.active .step-circle {
            border-color: var(--teal);
            color: var(--teal);
            background: #e6f7f7;
            box-shadow: 0 0 0 4px rgba(10,147,150,0.1);
        }

        .step-item.done { color: var(--teal-dark); }
        .step-item.done .step-circle {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff;
        }
        .step-item.done:not(:last-child)::after {
            background: var(--teal);
        }

        /* ===== WIZARD CARD ===== */
        .wizard-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 2rem;
            transition: box-shadow 0.2s ease;
        }
        .wizard-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        }

        .step-title {
            font-family: 'DM Sans', sans-serif;
            font-size: 1.35rem;
            color: var(--teal-dark);
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .step-title i { color: var(--teal); }

        /* ===== SELECTABLE CARDS (Sedes/Servicios/Médicos) ===== */
        .sel-card {
            background: #fff;
            border: 2px solid #dee2e6;
            border-radius: 0.85rem;
            padding: 1.1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .sel-card:hover {
            border-color: var(--teal);
            box-shadow: 0 4px 16px rgba(10,147,150,0.12);
            transform: translateY(-2px);
        }
        .sel-card.selected {
            border-color: var(--teal);
            background: linear-gradient(135deg, #f0fafa, #e6f7f7);
            box-shadow: 0 4px 16px rgba(10,147,150,0.15);
        }
        .sel-card.selected::after {
            content: '✓';
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 20px;
            height: 20px;
            background: var(--teal);
            color: #fff;
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .sel-card { position: relative; }

        .sel-card .card-name {
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            color: var(--teal-dark);
            margin-bottom: 0.15rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .sel-card .card-meta {
            font-size: 0.82rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .sel-card .card-meta i {
            font-size: 0.9rem;
            color: var(--teal);
        }

        /* ===== DATE INPUT ===== */
        .date-input {
            padding: 0.7rem 1rem;
            border: 2px solid #dee2e6;
            border-radius: 0.7rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
            max-width: 280px;
            background: #fff;
        }
        .date-input:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 4px rgba(10,147,150,0.12);
        }
        .date-input::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        .date-input::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        /* ===== TIME SLOTS ===== */
        .slot-btn {
            min-width: 85px;
            border: 2px solid #dee2e6;
            color: #495057;
            border-radius: 0.6rem;
            padding: 0.5rem 0.75rem;
            background: #fff;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .slot-btn:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: rgba(10,147,150,0.05);
        }
        .slot-btn.selected {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff;
            box-shadow: 0 4px 12px rgba(10,147,150,0.25);
        }
        .slot-btn:disabled {
            border-color: #e9ecef;
            color: #adb5bd;
            cursor: not-allowed;
            background: #f8f9fa;
            opacity: 0.7;
        }

        /* ===== RESUMEN ROWS ===== */
        .resumen-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }
        .resumen-row:last-child { border-bottom: none; }
        .resumen-row .label {
            color: #6c757d;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .resumen-row .label i { color: var(--teal); }
        .resumen-row .valor {
            font-weight: 600;
            color: var(--teal-dark);
        }

        /* ===== NAVIGATION BUTTONS ===== */
        .btn-prev {
            background: transparent;
            border: 2px solid #dee2e6;
            color: #6c757d;
            border-radius: 0.6rem;
            padding: 0.5rem 1.1rem;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn-prev:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: rgba(10,147,150,0.05);
        }

        .btn-next {
            background: var(--teal);
            color: #fff;
            border: none;
            border-radius: 0.6rem;
            padding: 0.55rem 1.4rem;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(10,147,150,0.25);
        }
        .btn-next:hover {
            background: var(--teal-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(10,147,150,0.35);
        }
        .btn-next:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ===== SUCCESS STATE ===== */
        .success-wrap {
            text-align: center;
            padding: 2.5rem 1.5rem;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #059669;
            box-shadow: 0 4px 16px rgba(5,150,105,0.2);
        }
        .success-icon i { font-size: 2.2rem; }

        .success-title {
            font-family: 'DM Sans', sans-serif;
            font-size: 1.6rem;
            color: #065f46;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        .success-wrap .text-muted {
            font-size: 0.95rem;
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
            .mc-navbar { padding: 0.75rem 1rem; }
            .step-bar { gap: 0.25rem; }
            .step-item span { display: none; }
            .step-circle { width: 32px; height: 32px; font-size: 0.8rem; }
            .step-item:not(:last-child)::after {
                left: calc(50% + 14px);
                right: calc(-50% + 14px);
            }
            .wizard-card { padding: 1.5rem; }
            .step-title { font-size: 1.2rem; }
            .sel-card { padding: 0.9rem; }
            .btn-next, .btn-prev { width: 100%; justify-content: center; }
            .d-flex.justify-content-between { flex-direction: column; gap: 0.75rem; }
        }

        @media (max-width: 480px) {
            .step-bar { flex-wrap: wrap; }
            .step-item { max-width: calc(33.333% - 4px); margin-bottom: 0.5rem; }
            .step-item:nth-child(n+4) { display: none; }
            .step-item:nth-child(3)::after { display: none; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="mc-navbar">
    <a class="mc-brand" href="<?= htmlspecialchars($w) ?>/views/cliente/dashboard.php">
        <i class="bi bi-heart-pulse-fill"></i>
        <span><em>Medi</em>Connect</span>
    </a>
    <span class="nav-label">
        <i class="bi bi-calendar-plus"></i> Nueva cita
    </span>
</nav>

<!-- Toast & Spinner -->
<div id="toast-box" role="region" aria-live="polite"></div>
<div id="spinner-overlay" aria-busy="true">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<!-- Main Content -->
<div class="container py-4 pb-5" style="max-width:1000px">

    <!-- Back Button -->
    <a href="<?= htmlspecialchars($w) ?>/views/cliente/dashboard.php" class="btn-back">
        <i class="bi bi-arrow-left"></i> Volver
    </a>

    <!-- Steps Progress Bar -->
    <!-- IDs/clases críticas preservadas: #step-bar, .step-item [data-step], .step-circle -->
    <div class="step-bar" id="step-bar">
        <div class="step-item active" data-step="1">
            <div class="step-circle">1</div>
            <span>Sede</span>
        </div>
        <div class="step-item" data-step="2">
            <div class="step-circle">2</div>
            <span>Servicio</span>
        </div>
        <div class="step-item" data-step="3">
            <div class="step-circle">3</div>
            <span>Médico</span>
        </div>
        <div class="step-item" data-step="4">
            <div class="step-circle">4</div>
            <span>Fecha</span>
        </div>
        <div class="step-item" data-step="5">
            <div class="step-circle">5</div>
            <span>Confirmar</span>
        </div>
    </div>

    <!-- Wizard Card -->
    <div class="wizard-card">

        <!-- PASO 1 · Sede -->
        <div id="step-1">
            <p class="step-title">
                <i class="bi bi-hospital"></i> ¿En qué sede te atenderás?
            </p>
            <div id="lista-sedes" class="row g-3"></div>
        </div>

        <!-- PASO 2 · Servicio -->
        <div id="step-2" class="d-none">
            <p class="step-title">
                <i class="bi bi-heart-pulse"></i> ¿Qué servicio necesitas?
            </p>
            <div id="lista-servicios" class="row g-3"></div>
            <div class="mt-4">
                <button class="btn-prev" data-prev="1">
                    <i class="bi bi-arrow-left"></i> Atrás
                </button>
            </div>
        </div>

        <!-- PASO 3 · Médico -->
        <div id="step-3" class="d-none">
            <p class="step-title">
                <i class="bi bi-person-medical"></i> Elige tu médico
            </p>
            <div id="lista-medicos" class="row g-3"></div>
            <div class="mt-4">
                <button class="btn-prev" data-prev="2">
                    <i class="bi bi-arrow-left"></i> Atrás
                </button>
            </div>
        </div>

        <!-- PASO 4 · Fecha y hora -->
        <div id="step-4" class="d-none">
            <p class="step-title">
                <i class="bi bi-calendar-week"></i> ¿Cuándo te viene bien?
            </p>

            <div class="mb-4">
                <label for="fecha-cita" class="form-label small fw-semibold text-muted"
                       style="text-transform:uppercase;letter-spacing:.04em;font-size:.75rem">
                    Selecciona una fecha
                </label>
                <br>
                <input type="date" id="fecha-cita" class="date-input">
            </div>

            <div id="slots-container" class="d-none">
                <label class="form-label small fw-semibold text-muted"
                       style="text-transform:uppercase;letter-spacing:.04em;font-size:.75rem">
                    Horarios disponibles
                </label>
                <div id="lista-slots" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>

            <div id="slots-empty" class="text-muted small d-none mt-2">
                <i class="bi bi-info-circle me-1"></i> No hay horarios disponibles para esta fecha.
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <button class="btn-prev" data-prev="3">
                    <i class="bi bi-arrow-left"></i> Atrás
                </button>
                <button class="btn-next d-none" id="btn-ir-confirmar">
                    Continuar <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- PASO 5 · Confirmar -->
        <div id="step-5" class="d-none">
            <p class="step-title">
                <i class="bi bi-check-circle"></i> Confirma tu cita
            </p>

            <div id="resumen-cita" class="mb-4"></div>

            <div class="d-flex justify-content-between">
                <button class="btn-prev" data-prev="4">
                    <i class="bi bi-arrow-left"></i> Atrás
                </button>
                <button class="btn-next" id="btn-agendar">
                    <span id="btn-agendar-txt">Confirmar cita</span>
                    <span class="spinner-border spinner-border-sm d-none" id="spin-agendar"></span>
                </button>
            </div>
        </div>

        <!-- ÉXITO -->
        <div id="step-ok" class="d-none success-wrap">
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <h2 class="success-title">¡Cita agendada!</h2>
            <p class="text-muted small mb-4">Tu cita ha sido registrada correctamente.</p>
            <a href="<?= htmlspecialchars($w) ?>/views/cliente/dashboard.php" class="btn-next" style="display:inline-flex">
                <i class="bi bi-calendar-check"></i> Ver mis citas
            </a>
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($w) ?>/assets/js/agendar.js?v=<?= time() ?>"></script>
<script>
    window.addEventListener('pageshow', e => { if (e.persisted) window.location.reload(); });
</script>
</body>
</html>