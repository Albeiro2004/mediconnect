<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class UsuarioModel
{
    private PDO $db;
    private const TABLE = 'usuarios';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre_completo, email, rol, fecha_registro
             FROM ' . self::TABLE . ' WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(string $nombre, string $email, string $password, string $rol = 'cliente'): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (nombre_completo, email, password, rol)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $email, $hash, $rol]);
        return (int)$this->db->lastInsertId();
    }

    // ── Listar todos (solo superadmin) ────────────────────────
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre_completo, email, rol, fecha_registro
             FROM ' . self::TABLE . ' ORDER BY fecha_registro DESC'
        );
        return $stmt->fetchAll();
    }

    public function update(int $id, string $nombre, string $email): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . ' SET nombre_completo = ?, email = ? WHERE id = ?'
        );
        return $stmt->execute([$nombre, $email, $id]);
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . ' SET password = ? WHERE id = ?'
        );
        return $stmt->execute([$hash, $id]);
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE email = ? AND id != ?'
        );
        $stmt->execute([$email, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
