<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class MedicoModel
{
    private PDO $db;
    private const TABLE      = 'medico';
    private const TBL_USERS  = 'usuarios';
    private const TBL_SEDES  = 'sedes';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(?int $sedeId = null): array
    {
        $sql = 'SELECT m.id, m.cargo_especialidad, m.perfil_profesional,
                       u.id AS usuario_id, u.nombre_completo, u.email,
                       s.id AS sede_id, s.nombre_sede, s.ciudad
                FROM ' . self::TABLE . ' m
                JOIN ' . self::TBL_USERS . ' u ON m.usuario_id = u.id
                JOIN ' . self::TBL_SEDES . ' s ON m.sede_id    = s.id';

        $params = [];
        if ($sedeId) {
            $sql   .= ' WHERE m.sede_id = ?';
            $params[] = $sedeId;
        }

        $sql .= ' ORDER BY u.nombre_completo ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, u.nombre_completo, u.email,
                    s.nombre_sede, s.ciudad
             FROM ' . self::TABLE . ' m
             JOIN ' . self::TBL_USERS . ' u ON m.usuario_id = u.id
             JOIN ' . self::TBL_SEDES . ' s ON m.sede_id    = s.id
             WHERE m.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByUsuarioId(int $usuarioId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE usuario_id = ? LIMIT 1'
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetch();
    }

    // ── Crear médico ──────────────────────────────────────────
    // Primero crea el usuario con rol 'prestador', luego el médico
    public function create(
        string $nombreCompleto,
        string $email,
        string $password,
        int    $sedeId,
        string $especialidad,
        string $perfil = ''
    ): int {
        $this->db->beginTransaction();

        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $stmt = $this->db->prepare(
                'INSERT INTO ' . self::TBL_USERS . ' (nombre_completo, email, password, rol)
                 VALUES (?, ?, ?, "prestador")'
            );
            $stmt->execute([$nombreCompleto, $email, $hash]);
            $usuarioId = (int)$this->db->lastInsertId();

            $stmt = $this->db->prepare(
                'INSERT INTO ' . self::TABLE . ' (usuario_id, sede_id, cargo_especialidad, perfil_profesional)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$usuarioId, $sedeId, $especialidad, $perfil ?: null]);
            $medicoId = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $medicoId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, int $sedeId, string $especialidad, string $perfil): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET sede_id = ?, cargo_especialidad = ?, perfil_profesional = ?
             WHERE id = ?'
        );
        return $stmt->execute([$sedeId, $especialidad, $perfil ?: null, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM citas
             WHERE medico_id = ? AND estado NOT IN ("cancelada", "finalizada")'
        );
        $stmt->execute([$id]);

        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException('No se puede eliminar: el médico tiene citas activas');
        }

        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getDisponiblesPorSede(int $sedeId): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.id, u.nombre_completo, u.email, m.cargo_especialidad, m.perfil_profesional, s.nombre_sede
             FROM ' . self::TABLE . ' m
             JOIN ' . self::TBL_USERS . ' u ON m.usuario_id = u.id
             JOIN ' . self::TBL_SEDES . ' s ON m.sede_id = s.id
             WHERE m.sede_id = ?
             ORDER BY u.nombre_completo ASC'
        );
        $stmt->execute([$sedeId]);
        return $stmt->fetchAll();
    }
}
