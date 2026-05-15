<?php
declare(strict_types=1);

/**
 * Shared PDO database connection for mobile API endpoints.
 */
function mobile_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('MOBILE_DB_HOST') ?: 'localhost';
    $port = getenv('MOBILE_DB_PORT') ?: '3306';
    $dbName = getenv('MOBILE_DB_NAME') ?: 'vapeshop_db';
    $username = getenv('MOBILE_DB_USER') ?: 'root';
    $password = getenv('MOBILE_DB_PASS') ?: '';

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
