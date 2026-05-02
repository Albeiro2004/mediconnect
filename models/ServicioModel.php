<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class ServicioModel
{
    private PDO $db;
    private const TABLE = 'servicios';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Listar todos los servicios ────────────────────────────
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM ' . self::TABLE . ' ORDER BY nombre_servicio ASC'
        );
        return $stmt->fetchAll();
    }

    // ── Buscar por ID ─────────────────────────────────────────
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Crear servicio ────────────────────────────────────────
    public function create(string $nombre, string $descripcion, float $precio, int $duracionMin): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (nombre_servicio, descripcion, precio, duracion_minutos)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $descripcion ?: null, $precio, $duracionMin]);
        return (int)$this->db->lastInsertId();
    }

    // ── Actualizar servicio ───────────────────────────────────
    public function update(int $id, string $nombre, string $descripcion, float $precio, int $duracionMin): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET nombre_servicio = ?, descripcion = ?, precio = ?, duracion_minutos = ?
             WHERE id = ?'
        );
        return $stmt->execute([$nombre, $descripcion ?: null, $precio, $duracionMin, $id]);
    }

    // ── Eliminar servicio ─────────────────────────────────────
    public function delete(int $id): bool
    {
        // Verificar si hay citas activas con este servicio
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM citas
             WHERE servicio_id = ? AND estado NOT IN ("cancelada", "finalizada")'
        );
        $stmt->execute([$id]);

        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException('No se puede eliminar: el servicio tiene citas activas asociadas');
        }

        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // ── Verificar si el nombre ya existe ──────────────────────
    public function nombreExiste(string $nombre, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE nombre_servicio = ? AND id != ?'
        );
        $stmt->execute([$nombre, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
