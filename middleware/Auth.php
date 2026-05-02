<?php

declare(strict_types=1);

require_once __DIR__ . '/../utils/helpers.php';

class Auth
{

    public static function check(): void
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            jsonResponse(401, ['error' => 'No autenticado. Inicia sesión por favor']);
        }
    }

    public static function requireRole(array $roles): void
    {
        self::check();

        if (!in_array($_SESSION['user_rol'] ?? '', $roles, true)) {
            jsonResponse(403, ['error' => 'Acceso denegado: permisos insuficientes']);
        }
    }

    public static function userId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    public static function userRol(): string
    {
        return (string)($_SESSION['user_rol'] ?? '');
    }
}
