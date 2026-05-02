<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ServicioModel.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class ServicioController
{
    private ServicioModel $model;

    public function __construct()
    {
        $this->model = new ServicioModel();
    }

    // ── GET /servicios  (público) ─────────────────────────────
    public function index(): void
    {
        $servicios = $this->model->getAll();
        jsonResponse(200, ['servicios' => $servicios]);
    }

    // ── GET /servicios/{id}  (público) ────────────────────────
    public function show(int $id): void
    {
        $servicio = $this->model->findById($id);
        $servicio
            ? jsonResponse(200, ['servicio' => $servicio])
            : jsonResponse(404, ['error' => 'Servicio no encontrado']);
    }

    // ── POST /admin/servicios  (superadmin | admin_sede) ──────
    public function store(): void
    {
        Auth::requireRole(['superadmin', 'admin_sede']);

        $data  = getJsonBody();
        $error = validateRequired($data, ['nombre_servicio', 'precio', 'duracion_minutos']);
        if ($error) jsonResponse(422, ['error' => $error]);

        $nombre = clean($data['nombre_servicio']);
        $precio = filter_var($data['precio'], FILTER_VALIDATE_FLOAT);

        if ($precio === false || $precio < 0) {
            jsonResponse(422, ['error' => 'El precio debe ser un número positivo']);
        }

        $duracion = (int)$data['duracion_minutos'];
        if ($duracion <= 0) {
            jsonResponse(422, ['error' => 'La duración debe ser mayor a 0 minutos']);
        }

        if ($this->model->nombreExiste($nombre)) {
            jsonResponse(409, ['error' => 'Ya existe un servicio con ese nombre']);
        }

        $id = $this->model->create(
            $nombre,
            clean($data['descripcion'] ?? ''),
            $precio,
            $duracion
        );

        jsonResponse(201, ['message' => 'Servicio creado correctamente', 'id' => $id]);
    }

    // ── PUT /admin/servicios/{id} ─────────────────────────────
    public function update(int $id): void
    {
        Auth::requireRole(['superadmin', 'admin_sede']);

        if (!$this->model->findById($id)) {
            jsonResponse(404, ['error' => 'Servicio no encontrado']);
        }

        $data  = getJsonBody();
        $error = validateRequired($data, ['nombre_servicio', 'precio', 'duracion_minutos']);
        if ($error) jsonResponse(422, ['error' => $error]);

        $nombre = clean($data['nombre_servicio']);
        $precio = filter_var($data['precio'], FILTER_VALIDATE_FLOAT);

        if ($precio === false || $precio < 0) {
            jsonResponse(422, ['error' => 'El precio debe ser un número positivo']);
        }

        $duracion = (int)$data['duracion_minutos'];
        if ($duracion <= 0) {
            jsonResponse(422, ['error' => 'La duración debe ser mayor a 0 minutos']);
        }

        if ($this->model->nombreExiste($nombre, $id)) {
            jsonResponse(409, ['error' => 'Ya existe otro servicio con ese nombre']);
        }

        $this->model->update($id, $nombre, clean($data['descripcion'] ?? ''), $precio, $duracion)
            ? jsonResponse(200, ['message' => 'Servicio actualizado correctamente'])
            : jsonResponse(500, ['error'   => 'No se pudo actualizar el servicio']);
    }

    // ── DELETE /admin/servicios/{id} ──────────────────────────
    public function destroy(int $id): void
    {
        Auth::requireRole(['superadmin']);

        try {
            $this->model->delete($id)
                ? jsonResponse(200, ['message' => 'Servicio eliminado correctamente'])
                : jsonResponse(404, ['error'   => 'Servicio no encontrado']);
        } catch (RuntimeException $e) {
            jsonResponse(409, ['error' => $e->getMessage()]);
        }
    }
}
