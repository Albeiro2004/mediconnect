<?php
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'cliente') {
    header('Location: /mediconnect/views/auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agendar cita · MediConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/main.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar mc-navbar px-3 mb-4">
        <a class="navbar-brand" href="/mediconnect/views/cliente/dashboard.php">
            <strong>Medi</strong>Connect
        </a>
        <span class="text-muted small">Agendar nueva cita</span>
    </nav>

    <!-- Toast y spinner -->
    <div id="toast-box"></div>
    <div id="spinner-overlay">
        <div class="spinner-border text-primary"></div>
    </div>

    <div class="container pb-5" style="max-width:720px">

        <a href="/mediconnect/views/cliente/dashboard.php"
            class="btn btn-sm btn-outline-secondary mb-4">← Volver</a>

        <!-- Indicador de pasos -->
        <div class="step-bar mb-4" id="step-bar">
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

        <div class="card mc-card p-4">

            <!-- PASO 1 · Sede ─────────────────────────────── -->
            <div id="step-1">
                <h6 class="fw-bold mb-3">1. Selecciona la sede</h6>
                <div id="lista-sedes" class="row g-3">
                    <!-- Se carga por JS -->
                </div>
            </div>

            <!-- PASO 2 · Servicio ─────────────────────────── -->
            <div id="step-2" class="d-none">
                <h6 class="fw-bold mb-3">2. Selecciona el servicio</h6>
                <div id="lista-servicios" class="row g-3"></div>
                <div class="mt-4 text-start">
                    <button class="btn btn-outline-secondary btn-sm" data-prev="1">← Atrás</button>
                </div>
            </div>

            <!-- PASO 3 · Médico ───────────────────────────── -->
            <div id="step-3" class="d-none">
                <h6 class="fw-bold mb-3">3. Selecciona el médico</h6>
                <div id="lista-medicos" class="row g-3"></div>
                <div class="mt-4 text-start">
                    <button class="btn btn-outline-secondary btn-sm" data-prev="2">← Atrás</button>
                </div>
            </div>

            <!-- PASO 4 · Fecha y hora ─────────────────────── -->
            <div id="step-4" class="d-none">
                <h6 class="fw-bold mb-3">4. Elige fecha y hora</h6>

                <div class="mb-3">
                    <label class="form-label small fw-semibold" for="fecha-cita">Fecha</label>
                    <input type="date" id="fecha-cita" class="form-control"
                        style="max-width:220px">
                </div>

                <div id="slots-container" class="d-none">
                    <label class="form-label small fw-semibold">Horarios disponibles</label>
                    <div id="lista-slots" class="d-flex flex-wrap gap-2 mt-1"></div>
                </div>

                <div id="slots-empty" class="text-muted small d-none mt-2">
                    No hay horarios disponibles para esta fecha.
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <button class="btn btn-outline-secondary btn-sm" data-prev="3">← Atrás</button>
                    <button class="btn btn-primary btn-mc btn-sm d-none" id="btn-ir-confirmar">
                        Continuar →
                    </button>
                </div>
            </div>

            <!-- PASO 5 · Confirmar ────────────────────────── -->
            <div id="step-5" class="d-none">
                <h6 class="fw-bold mb-3">5. Confirma tu cita</h6>

                <ul class="list-group list-group-flush mb-4" id="resumen-cita">
                    <!-- Se llena por JS -->
                </ul>

                <div class="d-flex justify-content-between">
                    <button class="btn btn-outline-secondary btn-sm" data-prev="4">← Atrás</button>
                    <button class="btn btn-primary btn-mc" id="btn-agendar">
                        <span id="btn-agendar-txt">Confirmar cita</span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" id="spin-agendar"></span>
                    </button>
                </div>
            </div>

            <!-- PASO 6 · Éxito ───────────────────────────── -->
            <div id="step-ok" class="d-none text-center py-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"
                    fill="#198754" viewBox="0 0 16 16" class="mb-3">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                </svg>
                <h5 class="fw-bold text-success">¡Cita agendada!</h5>
                <p class="text-muted small">Recibirás una confirmación en tu correo.</p>
                <a href="/mediconnect/views/cliente/dashboard.php"
                    class="btn btn-primary btn-mc btn-sm mt-2">Ver mis citas</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/mediconnect/assets/js/agendar.js"></script>
</body>

</html>