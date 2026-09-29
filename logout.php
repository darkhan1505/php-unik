<?php
require_once __DIR__ . '/includes/init.php';

$_SESSION = [];
session_destroy();

session_start();
set_flash('success', 'Вы вышли из системы.');
redirect('index.php');
