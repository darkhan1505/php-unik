<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/index.php');
}

$currentUser = current_user();
$userId = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;

if ($userId !== null && $userId !== (int) $currentUser['id']) {
    $stmt = getDB()->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    set_flash('success', $stmt->rowCount() > 0 ? 'Пользователь удалён.' : 'Пользователь не найден.');
} else {
    set_flash('error', 'Нельзя удалить самого себя.');
}

redirect('/admin/index.php');
