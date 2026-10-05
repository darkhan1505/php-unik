<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/index.php');
}

$currentUser = current_user();
$userId = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;
$role = $_POST['role'] ?? '';

if ($userId !== null && in_array($role, ['user', 'admin'], true) && $userId !== (int) $currentUser['id']) {
    $stmt = getDB()->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute([$role, $userId]);
    set_flash('success', 'Роль пользователя обновлена.');
} else {
    set_flash('error', 'Не удалось изменить роль пользователя.');
}

redirect('/admin/index.php');
