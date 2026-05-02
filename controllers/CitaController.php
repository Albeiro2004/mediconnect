<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/CitaModel.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class CitaController
{
    private CitaModel $model;

    public function __construct()
    {
        $this->model = new CitaModel();
    }

    public function store(): void
    {
        Auth::requireRole(['cliente', 'superadmin', 'admin_sede']);

        $data  = getJsonBody();
        $error = validateRequired($data, ['medico_id', 'servicio_id', 'sede_id', 'fecha_cita', 'hora_cita']);
        if ($error) jsonResponse(422, ['error' => $error]);

        // Validar formato de fecha y hora
        if (!strtotime($data['fecha_cita'])) {
            jsonResponse(422, ['error' => 'Formato de fecha inválido (YYYY-MM-DD)']);
        }

        try {
            $id = $this->model->create(
                Auth::userId(),
                (int)$data['medico_id'],
                (int)$data['servicio_id'],
                (int)$data['sede_id'],
                $data['fecha_cita'],
                $data['hora_cita']
            );
            jsonResponse(201, ['message' => 'Cita creada exitosamente', 'id' => $id]);
        } catch (RuntimeException $e) {
            jsonResponse(409, ['error' => $e->getMessage()]);
        }
    }

    // ── GET /citas  (mis citas como cliente) ──────────────────
    public function misCitas(): void
    {
        Auth::requireRole(['cliente']);
        $citas = $this->model->getByCliente(Auth::userId());
        jsonResponse(200, ['citas' => $citas]);
    }

    // ── GET /citas/medico/{id}  (agenda del médico) ───────────
    public function agendaMedico(int $medicoId): void
    {
        Auth::requireRole(['prestador', 'admin_sede', 'superadmin']);
        $fecha = $_GET['fecha'] ?? null;
        $citas = $this->model->getByMedico($medicoId, $fecha);
        jsonResponse(200, ['citas' => $citas]);
    }

    // ── GET /admin/citas  (todas las citas) ───────────────────
    public function index(): void
    {
        Auth::requireRole(['superadmin', 'admin_sede']);
        $estado = $_GET['estado'] ?? null;
        $citas  = $this->model->getAll($estado);
        jsonResponse(200, ['citas' => $citas]);
    }

    // ── PATCH /citas/{id}/estado  (admin cambia estado) ───────
    public function updateEstado(int $id): void
    {
        Auth::requireRole(['superadmin', 'admin_sede', 'prestador']);

        $data  = getJsonBody();
        $error = validateRequired($data, ['estado']);
        if ($error) jsonResponse(422, ['error' => $error]);

        try {
            $ok = $this->model->cambiarEstado($id, $data['estado']);
            $ok
                ? jsonResponse(200, ['message' => 'Estado actualizado correctamente'])
                : jsonResponse(404, ['error'   => 'Cita no encontrada']);
        } catch (InvalidArgumentException $e) {
            jsonResponse(422, ['error' => $e->getMessage()]);
        }
    }

    // ── DELETE /citas/{id}  (cliente cancela su cita) ─────────
    public function cancelar(int $id): void
    {
        Auth::requireRole(['cliente']);

        $ok = $this->model->cancelar($id, Auth::userId());
        $ok
            ? jsonResponse(200, ['message' => 'Cita cancelada correctamente'])
            : jsonResponse(404, ['error'   => 'Cita no encontrada o ya no se puede cancelar']);
    }
}
