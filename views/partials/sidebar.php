<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/paths.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol    = $_SESSION['user_rol']    ?? null;
$nombre = $_SESSION['user_nombre'] ?? 'Usuario';

// Ruta activa
$actual = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* ── Sidebar ─────────────────────────────────────────────── */
    .admin-layout {
        display: grid;
        grid-template-columns: 240px 1fr;
        min-height: 100vh;
    }

    .sidebar {
        background: var(--gray-800);
        color: var(--white);
        display: flex;
        flex-direction: column;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
    }

    .sidebar-brand {
        padding: 1.5rem 1.4rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        display: flex;
        align-items: center;
        gap: .7rem;
        font-family: var(--font-display);
        font-size: 1.3rem;
    }

    .sidebar-brand span {
        color: var(--accent);
    }

    .sidebar-user {
        padding: 1rem 1.4rem;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .sidebar-avatar {
        width: 36px;
        height: 36px;
        background: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .88rem;
        flex-shrink: 0;
    }

    .sidebar-user-info small {
        font-size: .72rem;
        color: var(--gray-400);
        display: block;
        text-transform: capitalize;
    }

    .sidebar-user-info strong {
        font-size: .85rem;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }

    .sidebar-nav {
        padding: 1rem 0;
        flex: 1;
    }

    .sidebar-section {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--gray-400);
        padding: .8rem 1.4rem .3rem;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem 1.4rem;
        color: rgba(255, 255, 255, .75);
        text-decoration: none;
        font-size: .9rem;
        font-weight: 500;
        transition: background var(--transition), color var(--transition);
        border-left: 3px solid transparent;
    }

    .sidebar-link i {
        font-size: 1.05rem;
        width: 20px;
        text-align: center;
    }

    .sidebar-link:hover {
        background: rgba(255, 255, 255, .07);
        color: var(--white);
    }

    .sidebar-link.active {
        background: rgba(11, 110, 79, .35);
        color: var(--white);
        border-left-color: var(--accent);
    }

    .sidebar-footer {
        padding: 1rem 1.4rem;
        border-top: 1px solid rgba(255, 255, 255, .08);
    }

    .sidebar-footer a {
        display: flex;
        align-items: center;
        gap: .6rem;
        color: rgba(255, 255, 255, .6);
        font-size: .88rem;
        text-decoration: none;
        transition: color var(--transition);
    }

    .sidebar-footer a:hover {
        color: var(--white);
    }

    /* ── Contenido principal ────────────────────────────────── */
    .admin-main {
        background: var(--gray-50);
        overflow-y: auto;
    }

    .admin-topbar {
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 50;
    }

    .admin-topbar h2 {
        font-size: 1.3rem;
        font-family: var(--font-body);
        font-weight: 700;
        color: var(--gray-800);
        margin: 0;
    }

    .admin-content {
        padding: 2rem;
    }

    /* ── Stat cards ────────────────────────────────────────── */
    .stat-card {
        background: var(--white);
        border-radius: var(--radius);
        padding: 1.4rem 1.6rem;
        border: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        gap: 1.2rem;
        transition: box-shadow var(--transition);
    }

    .stat-card:hover {
        box-shadow: var(--shadow);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon.green {
        background: var(--primary-light);
    }

    .stat-icon.amber {
        background: #FEF3C7;
    }

    .stat-icon.blue {
        background: #EFF6FF;
    }

    .stat-icon.red {
        background: #FEF2F2;
    }

    .stat-value {
        font-size: 1.9rem;
        font-weight: 700;
        line-height: 1;
        font-family: var(--font-display);
    }

    .stat-label {
        font-size: .82rem;
        color: var(--gray-400);
        margin-top: .2rem;
    }

    /* ── Tabla admin ───────────────────────────────────────── */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .9rem;
    }

    .admin-table th {
        background: var(--gray-50);
        border-bottom: 2px solid var(--gray-200);
        padding: .75rem 1rem;
        text-align: left;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--gray-400);
        white-space: nowrap;
    }

    .admin-table td {
        padding: .85rem 1rem;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
    }

    .admin-table tbody tr:hover {
        background: var(--gray-50);
    }

    .admin-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Botones de acción pequeños */
    .btn-action {
        padding: .3rem .65rem;
        border-radius: var(--radius-sm);
        font-size: .8rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        transition: opacity var(--transition);
    }

    .btn-action:hover {
        opacity: .82;
    }

    .btn-action.edit {
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .btn-action.danger {
        background: #FEF2F2;
        color: #B91C1C;
    }

    .btn-action.success {
        background: var(--primary-light);
        color: var(--primary-dark);
    }

    .btn-action.warning {
        background: #FEF3C7;
        color: #92400E;
    }

    /* ── Panel card ────────────────────────────────────────── */
    .panel-card {
        background: var(--white);
        border-radius: var(--radius);
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }

    .panel-card-header {
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-card-header h5 {
        font-family: var(--font-body);
        font-weight: 700;
        font-size: 1rem;
        margin: 0;
    }

    /* ── Modal admin ───────────────────────────────────────── */
    .modal-content {
        border: none;
        border-radius: var(--radius-lg) !important;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        border-bottom: 1px solid var(--gray-200);
        padding: 1.2rem 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--gray-200);
        padding: 1rem 1.5rem;
    }

    @media (max-width: 900px) {
        .admin-layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            display: none;
        }

        .admin-content {
            padding: 1rem;
        }
    }
</style>

<div class="admin-layout">
    <!-- ── Sidebar ── -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                <rect width="28" height="28" rx="8" fill="#0B6E4F" />
                <path d="M14 7v14M7 14h14" stroke="white" stroke-width="2.5" stroke-linecap="round" />
            </svg>
            Medi<span>Connect</span>
        </div>

        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr($nombre, 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <strong><?= htmlspecialchars($nombre) ?></strong>
                <small><?= htmlspecialchars(str_replace('_', ' ', $rol ?? '')) ?></small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">General</div>
            <a href="dashboard.php" class="sidebar-link <?= $actual === 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>

            <div class="sidebar-section">Gestión</div>
            <a href="citas.php" class="sidebar-link <?= $actual === 'citas.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check"></i> Citas
            </a>
            <a href="medicos.php" class="sidebar-link <?= $actual === 'medicos.php' ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i> Médicos
            </a>
            <a href="servicios.php" class="sidebar-link <?= $actual === 'servicios.php' ? 'active' : '' ?>">
                <i class="bi bi-heart-pulse"></i> Servicios
            </a>
            <a href="logs.php" class="sidebar-link <?= $actual === 'logs.php' ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i> Atenciones
            </a>

            <?php if ($rol === 'superadmin'): ?>
                <div class="sidebar-section">Sistema</div>
                <a href="sedes.php" class="sidebar-link <?= $actual === 'sedes.php' ? 'active' : '' ?>">
                    <i class="bi bi-building"></i> Sedes
                </a>
                <a href="usuarios.php" class="sidebar-link <?= $actual === 'usuarios.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Usuarios
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= htmlspecialchars(mc_web_base()) ?>/views/auth/logout.php">
                <i class="bi bi-box-arrow-left"></i> Cerrar sesión
            </a>
        </div>
    </aside>

    <!-- ── Main content (se cierra en cada vista) ── -->
    <main class="admin-main">