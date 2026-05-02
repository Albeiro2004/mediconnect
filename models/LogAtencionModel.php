<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class LogAtencionModel
{
    private PDO $db;
    private const TABLE     = 'logs_atencion';
    private const TBL_CITAS = 'citas';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Obtener log por ID de cita ────────────────────────────
    public function findByCita(int $citaId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT l.*,
                    c.fecha_cita, c.hora_cita, c.estado AS estado_cita,
                    uc.nombre_completo AS nombre_cliente,
                    um.nombre_completo AS nombre_medico,
                    s.nombre_servicio
             FROM ' . self::TABLE . ' l
             JOIN ' . self::TBL_CITAS . '              c  ON l.cita_id     = c.id
             JOIN usuarios          uc ON c.cliente_id  = uc.id
             JOIN medico            m  ON c.medico_id   = m.id
             JOIN usuarios          um ON m.usuario_id  = um.id
             JOIN servicios         s  ON c.servicio_id = s.id
             WHERE l.cita_id = ? LIMIT 1'
        );
        $stmt->execute([$citaId]);
        return $stmt->fetch();
    }

    // ── Obtener log por su propio ID ──────────────────────────
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Listar todos los logs (con info de cita) ──────────────
    public function getAll(?int $medicoId = null): array
    {
        $sql = 'SELECT l.*,
                       c.fecha_cita, c.hora_cita,
                       uc.nombre_completo AS nombre_cliente,
                       um.nombre_completo AS nombre_medico,
                       s.nombre_servicio
                FROM ' . self::TABLE . ' l
                JOIN ' . self::TBL_CITAS . '     c  ON l.cita_id     = c.id
                JOIN usuarios uc ON c.cliente_id  = uc.id
                JOIN medico   m  ON c.medico_id   = m.id
                JOIN usuarios um ON m.usuario_id  = um.id
                JOIN servicios s ON c.servicio_id = s.id';

        $params = [];
        if ($medicoId) {
            $sql     .= ' WHERE c.medico_id = ?';
            $params[] = $medicoId;
        }

        $sql .= ' ORDER BY c.fecha_cita DESC, c.hora_cita DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Historial de atenciones de un cliente ─────────────────
    public function getByCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*,
                    c.fecha_cita, c.hora_cita,
                    um.nombre_completo AS nombre_medico,
                    s.nombre_servicio,
                    se.nombre_sede
             FROM ' . self::TABLE . ' l
             JOIN ' . self::TBL_CITAS . '         c  ON l.cita_id     = c.id
             JOIN medico       m  ON c.medico_id   = m.id
             JOIN usuarios     um ON m.usuario_id  = um.id
             JOIN servicios    s  ON c.servicio_id = s.id
             JOIN sedes        se ON c.sede_id     = se.id
             WHERE c.cliente_id = ?
             ORDER BY c.fecha_cita DESC, c.hora_cita DESC'
        );
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    // ── Crear log de atención ─────────────────────────────────
    // Solo se puede crear si la cita está en estado 'finalizada'
    public function create(
        int    $citaId,
        string $observaciones,
        string $tratamiento,
        ?string $proximaCita
    ): int {
        // Verificar que la cita exista y esté finalizada
        $stmt = $this->db->prepare(
            'SELECT estado FROM ' . self::TBL_CITAS . ' WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$citaId]);
        $cita = $stmt->fetch();

        if (!$cita) {
            throw new RuntimeException('La cita no existe');
        }

        if ($cita['estado'] !== 'finalizada') {
            throw new RuntimeException('Solo se puede registrar atención en citas finalizadas');
        }

        // Verificar que no exista ya un log para esta cita
        if ($this->findByCita($citaId)) {
            throw new RuntimeException('Esta cita ya tiene un log de atención registrado');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . '
             (cita_id, observaciones_finales, tratamiento_o_resultado, proxima_cita_sugerida)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $citaId,
            $observaciones ?: null,
            $tratamiento   ?: null,
            $proximaCita   ?: null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    // ── Actualizar log de atención ────────────────────────────
    public function update(
        int    $id,
        string $observaciones,
        string $tratamiento,
        ?string $proximaCita
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET observaciones_finales    = ?,
                 tratamiento_o_resultado  = ?,
                 proxima_cita_sugerida    = ?
             WHERE id = ?'
        );
        return $stmt->execute([
            $observaciones ?: null,
            $tratamiento   ?: null,
            $proximaCita   ?: null,
            $id,
        ]);
    }

    // ── Eliminar log ──────────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
