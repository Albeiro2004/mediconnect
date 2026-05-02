<?php

declare(strict_types=1);

function jsonResponse(int $code, array $data): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    if (!is_array($data)) {
        jsonResponse(400, ['error' => 'El cuerpo de la solicitud debe ser JSON válido']);
    }

    return $data;
}

function validateRequired(array $data, array $fields): ?string
{
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
            return "El campo '{$field}' es obligatorio";
        }
    }
    return null;
}

function clean(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
