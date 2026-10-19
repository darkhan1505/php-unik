<?php

// session_start() включает $_SESSION — данные пользователя между запросами (связь через cookie).
// httponly — JS не видит cookie, samesite=Lax — базовая защита от CSRF.
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

// Подключаем конфиг БД и общие функции — теперь они доступны на любой странице.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
