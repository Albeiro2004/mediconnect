<?php

declare(strict_types=1);

class Database
{

    private static ?PDO $instance = null;

    private static string $host = 'localhost';
    private static string $db   = 'mediconnect';
    private static string $user = 'root';
    private static string $pass = '';
    private static string $charset = 'utf8mb4';

    private function __construct() {}

    public static function getInstance(): PDO
    {

        if (self::$instance === null) {

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$host,
                self::$db,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Error de conexión a la base de datos']));
            }
        }
        return self::$instance;
    }
}
