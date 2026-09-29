<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('categories.php');
}

$id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;

if ($id !== null) {
    $stmt = getDB()->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', $stmt->rowCount() > 0 ? 'Категория удалена.' : 'Категория не найдена.');
}

redirect('categories.php');
