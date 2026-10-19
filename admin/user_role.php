<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__ — папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции — он нужен каждой странице.
require_once __DIR__ . '/../includes/init.php';
// Пускает только администратора, остальных перенаправляет. Скрипт дальше не выполнится.
require_admin();

// $_SERVER['REQUEST_METHOD'] — HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // redirect() отправляет заголовок Location и вызывает exit — код ниже не выполнится.
    redirect('/admin/index.php');
}

$currentUser = current_user();
// $_GET — параметры из URL (?id=5), $_POST — поля отправленной формы. ctype_digit() — строка состоит только из цифр,
// (int) приводит к числу. Запись «условие ? а : б» — тернарный оператор (как if/else в одну строку).
$userId = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;
$role = $_POST['role'] ?? '';

// in_array($значение, $массив, true) — есть ли значение в массиве; true = строгое сравнение типов.
if ($userId !== null && in_array($role, ['user', 'admin'], true) && $userId !== (int) $currentUser['id']) {
    // prepare() + execute() — подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
    // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll() — все строки.
    $stmt = getDB()->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute([$role, $userId]);
    // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
    set_flash('success', 'Роль пользователя обновлена.');
} else {
    set_flash('error', 'Не удалось изменить роль пользователя.');
}

redirect('/admin/index.php');
