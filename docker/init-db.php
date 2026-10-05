<?php
require __DIR__ . '/../config/database.php';

$pdo = getDB();
$pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
echo "База данных инициализирована\n";
