<?php
// Настройки подключения к базе данных PostgreSQL.
// При необходимости измените значения под вашу локальную конфигурацию
// (или задайте переменные окружения DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASS).
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'php_unik');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');

/**
 * Возвращает единственное соединение PDO (singleton) на весь запрос.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Ошибка подключения к базе данных. Проверьте настройки в config/database.php и убедитесь, что база данных создана и схема импортирована (database/schema.sql). Подробности: ' . $e->getMessage());
        }
    }

    return $pdo;
}
