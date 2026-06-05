<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/LogAtencionModel.php';
require_once __DIR__ . '/../models/MedicoModel.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class LogAtencionController
{
    private LogAtencionModel $model;
    private MedicoModel      $medicoModel;

    public function __construct()
    {
        $this->model       = new LogAtencionModel();
        $this->medicoModel = new MedicoModel();
    }

    // ── GET /admin/logs  (todos los logs) ─────────────────────
    public function index(): void
    {
        Auth::requireRole(['superadmin', 'admin_sede']);

        $medicoId = isset($_GET['medico_id']) ? (int)$_GET['medico_id'] : null;
        $logs     = $this->model->getAll($medicoId);
        jsonResponse(200, ['logs' => $logs]);
    }

    // ── GET /citas/{id}/log  (log de una cita específica) ─────
    public function showByCita(int $citaId): void
    {
        Auth::check();

        $log = $this->model->findByCita($citaId);
        $log
            ? jsonResponse(200, ['log' => $log])
            : jsonResponse(404, ['error' => 'No existe log de atención para esta cita']);
    }

    // ── GET /historial  (historial del cliente autenticado) ───
    public function historialCliente(): void
    {
        Auth::requireRole(['cliente']);

        $logs = $this->model->getByCliente(Auth::userId());
        jsonResponse(200, ['historial' => $logs]);
    }

    // ── GET /medico/logs  (logs del médico autenticado) ───────
    public function logsMedico(): void
    {
        Auth::requireRole(['prestador']);

        $medico = $this->medicoModel->findByUsuarioId(Auth::userId());

        if (!$medico) {
            jsonResponse(404, ['error' => 'No se encontró perfil de médico para este usuario']);
        }

        $logs = $this->model->getAll((int)$medico['id']);
        jsonResponse(200, ['logs' => $logs]);
    }

    // ── POST /citas/{id}/log  (prestador registra atención) ───
    public function store(int $citaId): void
    {
        Auth::requireRole(['prestador', 'superadmin', 'admin_sede']);

        $data = getJsonBody();

        // Al menos observaciones o tratamiento deben estar presentes
        if (
            empty(trim($data['observaciones_finales']   ?? '')) &&
            empty(trim($data['tratamiento_o_resultado'] ?? ''))
        ) {
            jsonResponse(422, ['error' => 'Debe ingresar al menos observaciones o tratamiento']);
        }

        // Validar fecha sugerida si viene
        $proximaCita = $data['proxima_cita_sugerida'] ?? null;
        if ($proximaCita && !strtotime($proximaCita)) {
            jsonResponse(422, ['error' => 'Formato de fecha inválido para próxima cita (YYYY-MM-DD)']);
        }

        try {
            $id = $this->model->create(
                $citaId,
                clean($data['observaciones_finales']   ?? ''),
                clean($data['tratamiento_o_resultado'] ?? ''),
                $proximaCita
            );
            jsonResponse(201, ['message' => 'Log de atención registrado correctamente', 'id' => $id]);
        } catch (RuntimeException $e) {
            jsonResponse(409, ['error' => $e->getMessage()]);
        }
    }

    // ── PUT /logs/{id}  (actualizar log existente) ────────────
    public function update(int $id): void
    {
        Auth::requireRole(['prestador', 'superadmin']);

        $log = $this->model->findById($id);
        if (!$log) jsonResponse(404, ['error' => 'Log de atención no encontrado']);

        // El prestador solo puede editar sus propios logs
        if (Auth::userRol() === 'prestador') {
            $medico = $this->medicoModel->findByUsuarioId(Auth::userId());

            // Verificar que la cita pertenezca a este médico
            $stmt = Database::getInstance()->prepare(
                'SELECT medico_id FROM citas WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$log['cita_id']]);
            $cita = $stmt->fetch();

            if (!$medico || !$cita || (int)$cita['medico_id'] !== (int)$medico['id']) {
                jsonResponse(403, ['error' => 'No tienes permiso para editar este log']);
            }
        }

        $data = getJsonBody();

        if (
            empty(trim($data['observaciones_finales']   ?? '')) &&
            empty(trim($data['tratamiento_o_resultado'] ?? ''))
        ) {
            jsonResponse(422, ['error' => 'Debe ingresar al menos observaciones o tratamiento']);
        }

        $proximaCita = $data['proxima_cita_sugerida'] ?? null;
        if ($proximaCita && !strtotime($proximaCita)) {
            jsonResponse(422, ['error' => 'Formato de fecha inválido para próxima cita (YYYY-MM-DD)']);
        }

        $this->model->update(
            $id,
            clean($data['observaciones_finales']   ?? ''),
            clean($data['tratamiento_o_resultado'] ?? ''),
            $proximaCita
        )
            ? jsonResponse(200, ['message' => 'Log actualizado correctamente'])
            : jsonResponse(500, ['error'   => 'No se pudo actualizar el log']);
    }

    // ── DELETE /logs/{id}  (solo superadmin) ──────────────────
    public function destroy(int $id): void
    {
        Auth::requireRole(['superadmin']);

        $this->model->delete($id)
            ? jsonResponse(200, ['message' => 'Log eliminado correctamente'])
            : jsonResponse(404, ['error'   => 'Log no encontrado']);
    }
}
