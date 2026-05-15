<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/MedicoModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class MedicoController
{
    private MedicoModel  $model;
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        $this->model        = new MedicoModel();
        $this->usuarioModel = new UsuarioModel();
    }

    // ── GET /medicos  (público) ───────────────────────────────
    public function index(): void
    {
        $sedeId  = isset($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
        $medicos = $this->model->getAll($sedeId);
        jsonResponse(200, ['medicos' => $medicos]);
    }

    // ── GET /medicos/{id} ─────────────────────────────────────
    public function show(int $id): void
    {
        $medico = $this->model->findById($id);
        $medico
            ? jsonResponse(200, ['medico' => $medico])
            : jsonResponse(404, ['error' => 'Médico no encontrado']);
    }

    // ── GET /medicos/sede/{sedeId}  (médicos por sede) ────────
    public function porSede(int $sedeId): void
    {
        $medicos = $this->model->getDisponiblesPorSede($sedeId);
        jsonResponse(200, ['medicos' => $medicos]);
    }

    // ── POST /admin/medicos  (admin crea médico + usuario) ────
    public function store(): void
    {
        Auth::requireRole(['superadmin', 'admin_sede']);

        $data  = getJsonBody();
        $error = validateRequired($data, [
            'nombre_completo',
            'email',
            'password',
            'sede_id',
            'cargo_especialidad'
        ]);
        if ($error) jsonResponse(422, ['error' => $error]);

        $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            jsonResponse(422, ['error' => 'El email no tiene un formato válido']);
        }

        if ($this->usuarioModel->emailExists($email)) {
            jsonResponse(409, ['error' => 'El email ya está registrado']);
        }

        if (strlen($data['password']) < 8) {
            jsonResponse(422, ['error' => 'La contraseña debe tener al menos 8 caracteres']);
        }

        try {
            $id = $this->model->create(
                clean($data['nombre_completo']),
                $email,
                $data['password'],
                (int)$data['sede_id'],
                clean($data['cargo_especialidad']),
                clean($data['perfil_profesional'] ?? '')
            );
            jsonResponse(201, ['message' => 'Médico registrado correctamente', 'id' => $id]);
        } catch (Throwable $e) {
            jsonResponse(500, ['error' => 'Error al registrar médico: ' . $e->getMessage()]);
        }
    }

    // ── PUT /admin/medicos/{id} ───────────────────────────────
    public function update(int $id): void
    {
        Auth::requireRole(['superadmin', 'admin_sede']);

        $medico = $this->model->findById($id);
        if (!$medico) jsonResponse(404, ['error' => 'Médico no encontrado']);

        $data  = getJsonBody();
        $error = validateRequired($data, ['sede_id', 'cargo_especialidad']);
        if ($error) jsonResponse(422, ['error' => $error]);

        $this->model->update(
            $id,
            (int)$data['sede_id'],
            clean($data['cargo_especialidad']),
            clean($data['perfil_profesional'] ?? '')
        )
            ? jsonResponse(200, ['message' => 'Médico actualizado correctamente'])
            : jsonResponse(500, ['error'   => 'No se pudo actualizar el médico']);
    }

    // ── DELETE /admin/medicos/{id} ────────────────────────────
    public function destroy(int $id): void
    {
        Auth::requireRole(['superadmin']);

        try {
            $this->model->delete($id)
                ? jsonResponse(200, ['message' => 'Médico eliminado correctamente'])
                : jsonResponse(404, ['error'   => 'Médico no encontrado']);
        } catch (RuntimeException $e) {
            jsonResponse(409, ['error' => $e->getMessage()]);
        }
    }
}
