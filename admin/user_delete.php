<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__  папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции  он нужен каждой странице.
require_once __DIR__ . '/../includes/init.php';
// Пускает только администратора, остальных перенаправляет. Скрипт дальше не выполнится.
require_admin();

// $_SERVER['REQUEST_METHOD']  HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // redirect() отправляет заголовок Location и вызывает exit  код ниже не выполнится.
    redirect('/admin/index.php');
}

$currentUser = current_user();
// $_GET  параметры из URL (?id=5), $_POST  поля отправленной формы. ctype_digit()  строка состоит только из цифр,
// (int) приводит к числу. Запись «условие ? а : б»  тернарный оператор (как if/else в одну строку).
$userId = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;

if ($userId !== null && $userId !== (int) $currentUser['id']) {
    // prepare() + execute()  подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
    // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll()  все строки.
    $stmt = getDB()->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
    set_flash('success', $stmt->rowCount() > 0 ? 'Пользователь удалён.' : 'Пользователь не найден.');
} else {
    set_flash('error', 'Нельзя удалить самого себя.');
}

redirect('/admin/index.php');
