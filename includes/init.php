<?php

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
