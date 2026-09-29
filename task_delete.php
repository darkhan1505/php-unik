<?php
require_once __DIR__ . '/includes/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('tasks.php');
}

$user = current_user();
$taskId = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;

if ($taskId !== null) {
    $stmt = getDB()->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$taskId, $user['id']]);
    set_flash('success', $stmt->rowCount() > 0 ? 'Задача удалена.' : 'Задача не найдена.');
}

redirect('tasks.php');
