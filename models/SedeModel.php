<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class SedeModel
{
    private PDO $db;
    private const TABLE = 'sedes';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(?string $estado = null): array
    {
        $sql    = 'SELECT * FROM ' . self::TABLE;
        $params = [];

        if ($estado) {
            $sql   .= ' WHERE estado = ?';
            $params[] = $estado;
        }

        $sql .= ' ORDER BY nombre_sede ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(string $nombre, string $direccion, string $ciudad, string $telefono = ''): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (nombre_sede, direccion, ciudad, telefono_contacto)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $direccion, $ciudad, $telefono ?: null]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET nombre_sede = ?, direccion = ?, ciudad = ?, telefono_contacto = ?, estado = ?
             WHERE id = ?'
        );
        return $stmt->execute([
            $data['nombre_sede'],
            $data['direccion'],
            $data['ciudad'],
            $data['telefono_contacto'] ?? null,
            $data['estado'] ?? 'activa',
            $id,
        ]);
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, ['activa', 'inactiva'], true)) {
            throw new InvalidArgumentException('Estado inválido. Use: activa | inactiva');
        }

        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . ' SET estado = ? WHERE id = ?'
        );
        return $stmt->execute([$estado, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM citas
             WHERE sede_id = ? AND estado NOT IN ("cancelada", "finalizada")'
        );
        $stmt->execute([$id]);

        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException('No se puede eliminar: la sede tiene citas activas');
        }

        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function nombreExiste(string $nombre, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE nombre_sede = ? AND id != ?'
        );
        $stmt->execute([$nombre, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
