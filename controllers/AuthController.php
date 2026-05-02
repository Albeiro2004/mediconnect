<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../utils/helpers.php';

class AuthController
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
    }

    public function register(): void
    {
        $data = getJsonBody();

        $error = validateRequired($data, ['nombre_completo', 'email', 'password']);
        if ($error) {
            jsonResponse(422, ['error' => $error]);
        }

        $nombre   = clean($data['nombre_completo']);
        $email    = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
        $password = $data['password'];

        if (!$email) {
            jsonResponse(422, ['error' => 'El email no tiene un formato válido']);
        }

        if (strlen($password) < 8) {
            jsonResponse(422, ['error' => 'La contraseña debe tener al menos 8 caracteres']);
        }

        if ($this->model->emailExists($email)) {
            jsonResponse(409, ['error' => 'El email ya está registrado']);
        }

        $id = $this->model->create($nombre, $email, $password);

        jsonResponse(201, [
            'message' => 'Usuario registrado correctamente',
            'id'      => $id,
        ]);
    }

    public function login(): void
    {
        $data = getJsonBody();

        $error = validateRequired($data, ['email', 'password']);
        if ($error) {
            jsonResponse(422, ['error' => $error]);
        }

        $usuario = $this->model->findByEmail(trim($data['email']));

        if (!$usuario || !password_verify($data['password'], $usuario['password'])) {
            jsonResponse(401, ['error' => 'Credenciales incorrectas']);
        }

        // Iniciar sesión segura
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user_id']  = $usuario['id'];
        $_SESSION['user_rol'] = $usuario['rol'];
        $_SESSION['user_nombre'] = $usuario['nombre_completo'];

        jsonResponse(200, [
            'message' => 'Sesión iniciada correctamente',
            'usuario' => [
                'id'             => $usuario['id'],
                'nombre_completo' => $usuario['nombre_completo'],
                'email'          => $usuario['email'],
                'rol'            => $usuario['rol'],
            ],
        ]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        jsonResponse(200, ['message' => 'Sesión cerrada correctamente']);
    }

    public function me(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            jsonResponse(401, ['error' => 'No autenticado']);
        }

        $usuario = $this->model->findById((int)$_SESSION['user_id']);

        if (!$usuario) {
            jsonResponse(404, ['error' => 'Usuario no encontrado']);
        }

        jsonResponse(200, ['usuario' => $usuario]);
    }
}
