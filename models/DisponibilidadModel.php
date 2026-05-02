<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class DisponibilidadModel
{
    private PDO $db;
    private const TABLE = 'disponibilidad';

    private const DIAS_VALIDOS = [
        'Lunes',
        'Martes',
        'Miercoles',
        'Jueves',
        'Viernes',
        'Sabado',
        'Domingo'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Obtener disponibilidad de un médico ───────────────────
    public function getByMedico(int $medicoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . self::TABLE . '
             WHERE medico_id = ?
             ORDER BY FIELD(dia_semana, "Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"),
                      hora_inicio ASC'
        );
        $stmt->execute([$medicoId]);
        return $stmt->fetchAll();
    }

    // ── Obtener un bloque por ID ──────────────────────────────
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Agregar bloque de disponibilidad ──────────────────────
    public function create(int $medicoId, string $dia, string $horaInicio, string $horaFin): int
    {
        $this->validarDia($dia);
        $this->validarHoras($horaInicio, $horaFin);
        $this->verificarSolapamiento($medicoId, $dia, $horaInicio, $horaFin);

        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (medico_id, dia_semana, hora_inicio, hora_fin)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$medicoId, $dia, $horaInicio, $horaFin]);
        return (int)$this->db->lastInsertId();
    }

    // ── Actualizar bloque ─────────────────────────────────────
    public function update(int $id, string $dia, string $horaInicio, string $horaFin): bool
    {
        $bloque = $this->findById($id);
        if (!$bloque) {
            throw new RuntimeException('Bloque de disponibilidad no encontrado');
        }

        $this->validarDia($dia);
        $this->validarHoras($horaInicio, $horaFin);
        $this->verificarSolapamiento((int)$bloque['medico_id'], $dia, $horaInicio, $horaFin, $id);

        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET dia_semana = ?, hora_inicio = ?, hora_fin = ?
             WHERE id = ?'
        );
        return $stmt->execute([$dia, $horaInicio, $horaFin, $id]);
    }

    // ── Eliminar bloque ───────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // ── Eliminar toda la disponibilidad de un médico ──────────
    public function deleteByMedico(int $medicoId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM ' . self::TABLE . ' WHERE medico_id = ?'
        );
        return $stmt->execute([$medicoId]);
    }

    // ── Obtener horarios libres para agendar cita ─────────────
    // Devuelve los slots disponibles de un médico en una fecha dada
    public function getSlotsLibres(int $medicoId, string $fecha, int $duracionMin = 30): array
    {
        // Obtener el día de la semana en español
        $diasMap = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miercoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sabado',
            'Sunday'    => 'Domingo',
        ];

        $diaSemana = $diasMap[date('l', strtotime($fecha))] ?? null;

        if (!$diaSemana) {
            return [];
        }

        // Bloques del médico ese día
        $stmt = $this->db->prepare(
            'SELECT hora_inicio, hora_fin FROM ' . self::TABLE . '
             WHERE medico_id = ? AND dia_semana = ?'
        );
        $stmt->execute([$medicoId, $diaSemana]);
        $bloques = $stmt->fetchAll();

        if (empty($bloques)) {
            return [];
        }

        // Citas ya ocupadas ese día
        $stmt = $this->db->prepare(
            'SELECT hora_cita FROM citas
             WHERE medico_id = ? AND fecha_cita = ? AND estado NOT IN ("cancelada")'
        );
        $stmt->execute([$medicoId, $fecha]);
        $ocupadas = array_column($stmt->fetchAll(), 'hora_cita');

        // Generar slots cada $duracionMin minutos
        $slots = [];

        foreach ($bloques as $bloque) {
            $inicio  = strtotime($bloque['hora_inicio']);
            $fin     = strtotime($bloque['hora_fin']);
            $current = $inicio;

            while (($current + $duracionMin * 60) <= $fin) {
                $hora = date('H:i:s', $current);
                if (!in_array($hora, $ocupadas, true)) {
                    $slots[] = $hora;
                }
                $current += $duracionMin * 60;
            }
        }

        return $slots;
    }

    private function validarDia(string $dia): void
    {
        if (!in_array($dia, self::DIAS_VALIDOS, true)) {
            throw new InvalidArgumentException(
                'Día inválido. Valores permitidos: ' . implode(', ', self::DIAS_VALIDOS)
            );
        }
    }

    private function validarHoras(string $inicio, string $fin): void
    {
        if (strtotime($inicio) >= strtotime($fin)) {
            throw new InvalidArgumentException('La hora de inicio debe ser anterior a la hora de fin');
        }
    }

    private function verificarSolapamiento(
        int    $medicoId,
        string $dia,
        string $horaInicio,
        string $horaFin,
        int    $excludeId = 0
    ): void {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ' . self::TABLE . '
             WHERE medico_id = ? AND dia_semana = ? AND id != ?
               AND hora_inicio < ? AND hora_fin > ?'
        );
        $stmt->execute([$medicoId, $dia, $excludeId, $horaFin, $horaInicio]);

        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException('El bloque horario se solapa con uno ya existente');
        }
    }
}
