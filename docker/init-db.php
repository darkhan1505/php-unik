<?php
require __DIR__ . '/../config/database.php';

// Выполняем весь schema.sql одним запросом: file_get_contents читает файл в строку, exec() выполняет SQL.
$pdo = getDB();
$pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
echo "База данных инициализирована\n";
