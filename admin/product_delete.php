<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/products.php');
}

$id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;

if ($id !== null) {
    $stmt = getDB()->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', $stmt->rowCount() > 0 ? 'Куртка удалена.' : 'Куртка не найдена.');
}

redirect('/admin/products.php');
