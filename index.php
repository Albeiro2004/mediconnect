<?php

declare(strict_types=1);

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = '/mediconnect';

require_once __DIR__ . '/config/paths.php';

$w = mc_web_base();

if (str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}

$uri = str_replace('/index.php', '', $uri);

$uri = rtrim($uri, '/') ?: '/';

// Raíz del proyecto: panel según sesión o login (HTML, no API JSON)
if ($method === 'GET' && $uri === '/') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!empty($_SESSION['user_id'])) {
        $dest = match ($_SESSION['user_rol'] ?? '') {
            'superadmin', 'admin_sede' => '/mediconnect/views/admin/dashboard.php',
            'prestador'                => '/mediconnect/views/prestador/dashboard.php',
            default                    => '/mediconnect/views/cliente/dashboard.php',
        };
        header('Location: ' . $dest);
    } else {
        header('Location: ' . $w . '/views/auth/login.php');
    }
    exit;
}

// ── Cabeceras CORS / JSON ─────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://mediconnect.test');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

spm_autoload();

function spm_autoload(): void
{
    $dirs = [
        __DIR__ . '/config/',
        __DIR__ . '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/middleware/',
        __DIR__ . '/utils/',
    ];

    spl_autoload_register(function (string $class) use ($dirs): void {
        foreach ($dirs as $dir) {
            $file = $dir . $class . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    });

    require_once __DIR__ . '/utils/helpers.php';
}

// ── Router simple ─────────────────────────────────────────────
// Extraer segmentos
$segments = explode('/', ltrim($uri, '/'));  // ['auth','login']

match (true) {

    // AUTH
    $uri === '/auth/register' && $method === 'POST'
    => (new AuthController())->register(),

    $uri === '/auth/login' && $method === 'POST'
    => (new AuthController())->login(),

    $uri === '/auth/logout' && $method === 'POST'
    => (new AuthController())->logout(),

    $uri === '/auth/me' && $method === 'GET'
    => (new AuthController())->me(),

    // CITAS – cliente
    $uri === '/citas' && $method === 'POST'
    => (new CitaController())->store(),

    $uri === '/citas' && $method === 'GET'
    => (new CitaController())->misCitas(),

    // CITAS – admin
    $uri === '/admin/citas' && $method === 'GET'
    => (new CitaController())->index(),

    // CITAS – medico agenda  GET /citas/medico/{id}
    $segments[0] === 'citas' && ($segments[1] ?? '') === 'medico' && isset($segments[2]) && $method === 'GET'
    => (new CitaController())->agendaMedico((int)$segments[2]),

    // CITAS – cambiar estado  PATCH /citas/{id}/estado
    $segments[0] === 'citas' && isset($segments[1]) && ($segments[2] ?? '') === 'estado' && $method === 'PATCH'
    => (new CitaController())->updateEstado((int)$segments[1]),

    // CITAS – cancelar  DELETE /citas/{id}
    $segments[0] === 'citas' && isset($segments[1]) && $method === 'DELETE'
    => (new CitaController())->cancelar((int)$segments[1]),

    // ── SERVICIOS (público) ──────────────────────────────────
    $uri === '/servicios' && $method === 'GET'
    => (new ServicioController())->index(),

    $segments[0] === 'servicios' && isset($segments[1]) && $method === 'GET'
    => (new ServicioController())->show((int)$segments[1]),

    // ── SERVICIOS (admin) ─────────────────────────────────────
    $uri === '/admin/servicios' && $method === 'POST'
    => (new ServicioController())->store(),

    $segments[0] === 'admin' && ($segments[1] ?? '') === 'servicios' && isset($segments[2]) && $method === 'PUT'
    => (new ServicioController())->update((int)$segments[2]),

    $segments[0] === 'admin' && ($segments[1] ?? '') === 'servicios' && isset($segments[2]) && $method === 'DELETE'
    => (new ServicioController())->destroy((int)$segments[2]),

    // ── LOGS DE ATENCIÓN ──────────────────────────────────────
    // GET  /admin/logs
    $uri === '/admin/logs' && $method === 'GET'
    => (new LogAtencionController())->index(),

    // GET  /citas/{id}/log
    $segments[0] === 'citas' && isset($segments[1]) && ($segments[2] ?? '') === 'log' && $method === 'GET'
    => (new LogAtencionController())->showByCita((int)$segments[1]),

    // POST /citas/{id}/log
    $segments[0] === 'citas' && isset($segments[1]) && ($segments[2] ?? '') === 'log' && $method === 'POST'
    => (new LogAtencionController())->store((int)$segments[1]),

    // GET  /historial  (cliente autenticado)
    $uri === '/historial' && $method === 'GET'
    => (new LogAtencionController())->historialCliente(),

    // GET  /medico/logs  (médico autenticado)
    $uri === '/medico/logs' && $method === 'GET'
    => (new LogAtencionController())->logsMedico(),

    // PUT  /logs/{id}
    $segments[0] === 'logs' && isset($segments[1]) && $method === 'PUT'
    => (new LogAtencionController())->update((int)$segments[1]),

    // DELETE /logs/{id}
    $segments[0] === 'logs' && isset($segments[1]) && $method === 'DELETE'
    => (new LogAtencionController())->destroy((int)$segments[1]),

    // ── SEDES (público) ──────────────────────────────────────
    $uri === '/sedes' && $method === 'GET'
    => (new SedeController())->index(),

    $segments[0] === 'sedes' && isset($segments[1]) && $method === 'GET'
    => (new SedeController())->show((int)$segments[1]),

    // ── SEDES (admin) ─────────────────────────────────────────
    $uri === '/admin/sedes' && $method === 'POST'
    => (new SedeController())->store(),

    $segments[0] === 'admin' && ($segments[1] ?? '') === 'sedes' && isset($segments[2]) && $method === 'PUT'
    => (new SedeController())->update((int)$segments[2]),

    $segments[0] === 'admin' && ($segments[1] ?? '') === 'sedes' && isset($segments[2])
        && ($segments[3] ?? '') === 'estado' && $method === 'PATCH'
    => (new SedeController())->cambiarEstado((int)$segments[2]),

    $segments[0] === 'admin' && ($segments[1] ?? '') === 'sedes' && isset($segments[2]) && $method === 'DELETE'
    => (new SedeController())->destroy((int)$segments[2]),

    // ── MÉDICOS (público) ─────────────────────────────────────
    $uri === '/medicos' && $method === 'GET'
    => (new MedicoController())->index(),

    $segments[0] === 'medicos' && isset($segments[1]) && !isset($segments[2]) && $method === 'GET'
    => (new MedicoController())->show((int)$segments[1]),

    $segments[0] === 'medicos' && ($segments[1] ?? '') === 'sede' && isset($segments[2]) && $method === 'GET'
    => (new MedicoController())->porSede((int)$segments[2]),

    // ── MÉDICOS (admin) ───────────────────────────────────────
    $uri === '/admin/medicos' && $method === 'POST'
    => (new MedicoController())->store(),

    $segments[0] === 'admin' && ($segments[1] ?? '') === 'medicos' && isset($segments[2]) && $method === 'PUT'
    => (new MedicoController())->update((int)$segments[2]),

    $segments[0] === 'admin' && ($segments[1] ?? '') === 'medicos' && isset($segments[2]) && $method === 'DELETE'
    => (new MedicoController())->destroy((int)$segments[2]),

    // ── DISPONIBILIDAD ────────────────────────────────────────
    // GET /medicos/{id}/disponibilidad
    $segments[0] === 'medicos' && isset($segments[1]) && ($segments[2] ?? '') === 'disponibilidad' && $method === 'GET'
    => (new DisponibilidadController())->index((int)$segments[1]),

    // GET /medicos/{id}/slots?fecha=YYYY-MM-DD
    $segments[0] === 'medicos' && isset($segments[1]) && ($segments[2] ?? '') === 'slots' && $method === 'GET'
    => (new DisponibilidadController())->slots((int)$segments[1]),

    // POST /admin/medicos/{id}/disponibilidad
    $segments[0] === 'admin' && ($segments[1] ?? '') === 'medicos'
        && isset($segments[2]) && ($segments[3] ?? '') === 'disponibilidad' && $method === 'POST'
    => (new DisponibilidadController())->store((int)$segments[2]),

    // PUT /admin/disponibilidad/{id}
    $segments[0] === 'admin' && ($segments[1] ?? '') === 'disponibilidad' && isset($segments[2]) && $method === 'PUT'
    => (new DisponibilidadController())->update((int)$segments[2]),

    // DELETE /admin/disponibilidad/{id}
    $segments[0] === 'admin' && ($segments[1] ?? '') === 'disponibilidad' && isset($segments[2]) && $method === 'DELETE'
    => (new DisponibilidadController())->destroy((int)$segments[2]),

    // 404
    default => jsonResponse(404, ['error' => 'Ruta no encontrada'])
};
