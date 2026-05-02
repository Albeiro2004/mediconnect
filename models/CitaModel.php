<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class CitaModel
{
    private PDO $db;
    private const TABLE = 'citas';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $clienteId, int $medicoId, int $servicioId, int $sedeId, string $fecha, string $hora): int
    {
        if ($this->existeConflicto($medicoId, $fecha, $hora)) {
            throw new RuntimeException('El médico ya tiene una cita en ese horario');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (cliente_id, medico_id, servicio_id, sede_id, fecha_cita, hora_cita)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$clienteId, $medicoId, $servicioId, $sedeId, $fecha, $hora]);
        return (int)$this->db->lastInsertId();
    }

    public function getByCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*,
                    u.nombre_completo AS nombre_medico,
                    s.nombre_servicio,
                    se.nombre_sede
             FROM ' . self::TABLE . ' c
             JOIN medico    m  ON c.medico_id   = m.id
             JOIN usuarios  u  ON m.usuario_id  = u.id
             JOIN servicios s  ON c.servicio_id = s.id
             JOIN sedes     se ON c.sede_id     = se.id
             WHERE c.cliente_id = ?
             ORDER BY c.fecha_cita DESC, c.hora_cita DESC'
        );
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public function getByMedico(int $medicoId, ?string $fecha = null): array
    {
        $sql = 'SELECT c.*,
                       u.nombre_completo AS nombre_cliente,
                       s.nombre_servicio
                FROM ' . self::TABLE . ' c
                JOIN usuarios  u ON c.cliente_id  = u.id
                JOIN servicios s ON c.servicio_id = s.id
                WHERE c.medico_id = ?';

        $params = [$medicoId];

        if ($fecha) {
            $sql .= ' AND c.fecha_cita = ?';
            $params[] = $fecha;
        }

        $sql .= ' ORDER BY c.fecha_cita ASC, c.hora_cita ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAll(?string $estado = null): array
    {
        $sql = 'SELECT c.*,
                       uc.nombre_completo AS nombre_cliente,
                       um.nombre_completo AS nombre_medico,
                       s.nombre_servicio,
                       se.nombre_sede
                FROM ' . self::TABLE . ' c
                JOIN usuarios  uc ON c.cliente_id  = uc.id
                JOIN medico    m  ON c.medico_id   = m.id
                JOIN usuarios  um ON m.usuario_id  = um.id
                JOIN servicios s  ON c.servicio_id = s.id
                JOIN sedes     se ON c.sede_id     = se.id';

        $params = [];
        if ($estado) {
            $sql .= ' WHERE c.estado = ?';
            $params[] = $estado;
        }

        $sql .= ' ORDER BY c.fecha_cita DESC, c.hora_cita DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $estados = ['pendiente', 'confirmada', 'cancelada', 'finalizada'];
        if (!in_array($estado, $estados, true)) {
            throw new InvalidArgumentException('Estado no válido');
        }

        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . ' SET estado = ? WHERE id = ?'
        );
        return $stmt->execute([$estado, $id]);
    }

    public function cancelar(int $id, int $clienteId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . ' SET estado = "cancelada"
             WHERE id = ? AND cliente_id = ? AND estado = "pendiente"'
        );
        $stmt->execute([$id, $clienteId]);
        return $stmt->rowCount() > 0;
    }

    private function existeConflicto(int $medicoId, string $fecha, string $hora): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ' . self::TABLE . '
             WHERE medico_id = ? AND fecha_cita = ? AND hora_cita = ?
               AND estado NOT IN ("cancelada")'
        );
        $stmt->execute([$medicoId, $fecha, $hora]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
