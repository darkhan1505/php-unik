<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__  папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции  он нужен каждой странице.
require_once __DIR__ . '/../includes/init.php';
// Пускает только администратора, остальных перенаправляет. Скрипт дальше не выполнится.
require_admin();

// getDB() возвращает объект PDO  соединение с PostgreSQL (см. config/database.php).
$db = getDB();
$statuses = ['new', 'shipped', 'done', 'cancelled'];

// $_SERVER['REQUEST_METHOD']  HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $_GET  параметры из URL (?id=5), $_POST  поля отправленной формы. ctype_digit()  строка состоит только из цифр,
    // (int) приводит к числу. Запись «условие ? а : б»  тернарный оператор (как if/else в одну строку).
    $orderId = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;
    $status = $_POST['status'] ?? '';

    // in_array($значение, $массив, true)  есть ли значение в массиве; true = строгое сравнение типов.
    if ($orderId !== null && in_array($status, $statuses, true)) {
        // prepare() + execute()  подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
        // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll()  все строки.
        $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
        set_flash('success', 'Статус заказа обновлён.');
    } else {
        set_flash('error', 'Не удалось изменить статус.');
    }
    // redirect() отправляет заголовок Location и вызывает exit  код ниже не выполнится.
    redirect('/admin/orders.php');
}

// query()  запрос без параметров. fetchAll() возвращает все строки: массив ассоциативных массивов ['name' => '...'].
$orders = $db->query(
    'SELECT o.id, o.full_name, o.phone, o.address, o.total, o.status, o.created_at, u.email
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.id DESC'
)->fetchAll();

$itemsByOrder = [];
if ($orders) {
    // foreach ($массив as $элемент)  цикл по массиву.
    foreach ($db->query('SELECT order_id, product_name, size, quantity FROM order_items ORDER BY id')->fetchAll() as $row) {
        // [] = добавляет элемент в конец массива; так группируем позиции по номеру заказа.
        $itemsByOrder[$row['order_id']][] = $row;
    }
}

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Заказы';
// require вставляет файл прямо сюда (в отличие от require_once  каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
require __DIR__ . '/../includes/header.php';
?>

<section class="container section-sm">
  <h2>Заказы</h2>
  <?php require __DIR__ . '/_nav.php'; ?>

  <div class="table-wrap">
    <table>
      <thead><tr><th>№</th><th>Клиент</th><th>Состав</th><th>Сумма</th><th>Статус</th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><b>#<?= (int) $o['id'] ?></b><br><span class="muted"><?= e(format_datetime($o['created_at'])) ?></span></td>
            <td>
              <b><?= e($o['full_name']) ?></b><br>
              <?= e($o['phone']) ?><br>
              <span class="muted"><?= e($o['address']) ?><br><?= e($o['email']) ?></span>
            </td>
            <td>
              <?php foreach ($itemsByOrder[$o['id']] ?? [] as $it): ?>
                <div><?= e($it['product_name']) ?> (<?= e($it['size']) ?>) × <?= (int) $it['quantity'] ?></div>
              <?php endforeach; ?>
            </td>
            <td><b><?= format_price((int) $o['total']) ?></b></td>
            <td>
              <form method="post" class="actions">
                <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                <select name="status" style="width:auto; padding:8px 10px;">
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e(order_status_label($s)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-light btn-sm">ОК</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?>
          <tr><td colspan="5" class="muted">Заказов пока нет.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
