<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/DisponibilidadModel.php';
require_once __DIR__ . '/../models/MedicoModel.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class DisponibilidadController
{
    private DisponibilidadModel $model;
    private MedicoModel         $medicoModel;

    public function __construct()
    {
        $this->model       = new DisponibilidadModel();
        $this->medicoModel = new MedicoModel();
    }

    // ── GET /medicos/{id}/disponibilidad  (público) ───────────
    public function index(int $medicoId): void
    {
        if (!$this->medicoModel->findById($medicoId)) {
            jsonResponse(404, ['error' => 'Médico no encontrado']);
        }

        $bloques = $this->model->getByMedico($medicoId);
        jsonResponse(200, ['disponibilidad' => $bloques]);
    }

    // ── GET /medicos/{id}/slots?fecha=YYYY-MM-DD  (público) ───
    public function slots(int $medicoId): void
    {
        $fecha = $_GET['fecha'] ?? null;

        if (!$fecha || !strtotime($fecha)) {
            jsonResponse(422, ['error' => 'Parámetro "fecha" requerido (formato: YYYY-MM-DD)']);
        }

        if (!$this->medicoModel->findById($medicoId)) {
            jsonResponse(404, ['error' => 'Médico no encontrado']);
        }

        $duracion = (int)($_GET['duracion'] ?? 30);
        $slots    = $this->model->getSlotsLibres($medicoId, $fecha, $duracion);

        jsonResponse(200, [
            'medico_id' => $medicoId,
            'fecha'     => $fecha,
            'slots'     => $slots,
        ]);
    }

    // ── POST /admin/medicos/{id}/disponibilidad ───────────────
    public function store(int $medicoId): void
    {
        Auth::requireRole(['superadmin', 'admin_sede', 'prestador']);

        // El prestador solo puede editar su propia disponibilidad
        if (Auth::userRol() === 'prestador') {
            $medicoPropio = $this->medicoModel->findByUsuarioId(Auth::userId());
            if (!$medicoPropio || (int)$medicoPropio['id'] !== $medicoId) {
                jsonResponse(403, ['error' => 'Solo puedes gestionar tu propia disponibilidad']);
            }
        }

        if (!$this->medicoModel->findById($medicoId)) {
            jsonResponse(404, ['error' => 'Médico no encontrado']);
        }

        $data  = getJsonBody();
        $error = validateRequired($data, ['dia_semana', 'hora_inicio', 'hora_fin']);
        if ($error) jsonResponse(422, ['error' => $error]);

        try {
            $id = $this->model->create(
                $medicoId,
                $data['dia_semana'],
                $data['hora_inicio'],
                $data['hora_fin']
            );
            jsonResponse(201, ['message' => 'Disponibilidad registrada correctamente', 'id' => $id]);
        } catch (InvalidArgumentException | RuntimeException $e) {
            jsonResponse(422, ['error' => $e->getMessage()]);
        }
    }

    // ── PUT /admin/disponibilidad/{id} ────────────────────────
    public function update(int $id): void
    {
        Auth::requireRole(['superadmin', 'admin_sede', 'prestador']);

        $bloque = $this->model->findById($id);
        if (!$bloque) jsonResponse(404, ['error' => 'Bloque de disponibilidad no encontrado']);

        // El prestador solo puede editar su propio bloque
        if (Auth::userRol() === 'prestador') {
            $medicoPropio = $this->medicoModel->findByUsuarioId(Auth::userId());
            if (!$medicoPropio || (int)$medicoPropio['id'] !== (int)$bloque['medico_id']) {
                jsonResponse(403, ['error' => 'Solo puedes gestionar tu propia disponibilidad']);
            }
        }

        $data  = getJsonBody();
        $error = validateRequired($data, ['dia_semana', 'hora_inicio', 'hora_fin']);
        if ($error) jsonResponse(422, ['error' => $error]);

        try {
            $this->model->update($id, $data['dia_semana'], $data['hora_inicio'], $data['hora_fin'])
                ? jsonResponse(200, ['message' => 'Disponibilidad actualizada correctamente'])
                : jsonResponse(500, ['error'   => 'No se pudo actualizar']);
        } catch (InvalidArgumentException | RuntimeException $e) {
            jsonResponse(422, ['error' => $e->getMessage()]);
        }
    }

    // ── DELETE /admin/disponibilidad/{id} ─────────────────────
    public function destroy(int $id): void
    {
        Auth::requireRole(['superadmin', 'admin_sede', 'prestador']);

        $bloque = $this->model->findById($id);
        if (!$bloque) jsonResponse(404, ['error' => 'Bloque no encontrado']);

        if (Auth::userRol() === 'prestador') {
            $medicoPropio = $this->medicoModel->findByUsuarioId(Auth::userId());
            if (!$medicoPropio || (int)$medicoPropio['id'] !== (int)$bloque['medico_id']) {
                jsonResponse(403, ['error' => 'Solo puedes gestionar tu propia disponibilidad']);
            }
        }

        $this->model->delete($id)
            ? jsonResponse(200, ['message' => 'Bloque eliminado correctamente'])
            : jsonResponse(500, ['error'   => 'No se pudo eliminar el bloque']);
    }
}
