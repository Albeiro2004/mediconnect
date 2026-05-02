<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/SedeModel.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class SedeController
{
    private SedeModel $model;

    public function __construct()
    {
        $this->model = new SedeModel();
    }

    // ── GET /sedes  (público: listar sedes activas) ───────────
    public function index(): void
    {
        $estado = $_GET['estado'] ?? 'activa';
        $sedes  = $this->model->getAll($estado);
        jsonResponse(200, ['sedes' => $sedes]);
    }

    // ── GET /sedes/{id} ───────────────────────────────────────
    public function show(int $id): void
    {
        $sede = $this->model->findById($id);
        $sede
            ? jsonResponse(200, ['sede' => $sede])
            : jsonResponse(404, ['error' => 'Sede no encontrada']);
    }

    // ── POST /admin/sedes  (superadmin crea sede) ─────────────
    public function store(): void
    {
        Auth::requireRole(['superadmin']);

        $data  = getJsonBody();
        $error = validateRequired($data, ['nombre_sede', 'direccion', 'ciudad']);
        if ($error) jsonResponse(422, ['error' => $error]);

        $nombre = clean($data['nombre_sede']);

        if ($this->model->nombreExiste($nombre)) {
            jsonResponse(409, ['error' => 'Ya existe una sede con ese nombre']);
        }

        $id = $this->model->create(
            $nombre,
            clean($data['direccion']),
            clean($data['ciudad']),
            clean($data['telefono_contacto'] ?? '')
        );

        jsonResponse(201, ['message' => 'Sede creada correctamente', 'id' => $id]);
    }

    // ── PUT /admin/sedes/{id}  (superadmin actualiza sede) ────
    public function update(int $id): void
    {
        Auth::requireRole(['superadmin']);

        $sede = $this->model->findById($id);
        if (!$sede) jsonResponse(404, ['error' => 'Sede no encontrada']);

        $data  = getJsonBody();
        $error = validateRequired($data, ['nombre_sede', 'direccion', 'ciudad']);
        if ($error) jsonResponse(422, ['error' => $error]);

        $nombre = clean($data['nombre_sede']);

        if ($this->model->nombreExiste($nombre, $id)) {
            jsonResponse(409, ['error' => 'Ya existe otra sede con ese nombre']);
        }

        $data['nombre_sede'] = $nombre;
        $data['direccion']   = clean($data['direccion']);
        $data['ciudad']      = clean($data['ciudad']);

        $this->model->update($id, $data)
            ? jsonResponse(200, ['message' => 'Sede actualizada correctamente'])
            : jsonResponse(500, ['error'   => 'No se pudo actualizar la sede']);
    }

    // ── PATCH /admin/sedes/{id}/estado ────────────────────────
    public function cambiarEstado(int $id): void
    {
        Auth::requireRole(['superadmin']);

        $data  = getJsonBody();
        $error = validateRequired($data, ['estado']);
        if ($error) jsonResponse(422, ['error' => $error]);

        try {
            $this->model->cambiarEstado($id, $data['estado'])
                ? jsonResponse(200, ['message' => 'Estado actualizado correctamente'])
                : jsonResponse(404, ['error'   => 'Sede no encontrada']);
        } catch (InvalidArgumentException $e) {
            jsonResponse(422, ['error' => $e->getMessage()]);
        }
    }

    // ── DELETE /admin/sedes/{id} ──────────────────────────────
    public function destroy(int $id): void
    {
        Auth::requireRole(['superadmin']);

        try {
            $this->model->delete($id)
                ? jsonResponse(200, ['message' => 'Sede eliminada correctamente'])
                : jsonResponse(404, ['error'   => 'Sede no encontrada']);
        } catch (RuntimeException $e) {
            jsonResponse(409, ['error' => $e->getMessage()]);
        }
    }
}
