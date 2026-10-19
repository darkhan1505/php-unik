<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__  папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции  он нужен каждой странице.
require_once __DIR__ . '/../includes/init.php';
// Пускает только администратора, остальных перенаправляет. Скрипт дальше не выполнится.
require_admin();

// getDB() возвращает объект PDO  соединение с PostgreSQL (см. config/database.php).
$db = getDB();
$currentUser = current_user();

// query()  запрос без параметров. fetchAll() возвращает все строки: массив ассоциативных массивов ['name' => '...'].
$userCount = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
// fetchColumn()  значение первого столбца первой строки (удобно для COUNT/SUM).
$productCount = (int) $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
$orderCount = (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$revenue = (int) $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled'")->fetchColumn();

$users = $db->query(
    'SELECT u.id, u.name, u.email, u.role, u.created_at, COUNT(o.id) AS order_count
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC'
)->fetchAll();

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Админ-панель';
// require вставляет файл прямо сюда (в отличие от require_once  каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
require __DIR__ . '/../includes/header.php';
?>

<section class="container section-sm">
  <h2>Админ-панель</h2>
  <?php require __DIR__ . '/_nav.php'; ?>

  <div class="stats">
    <div class="stat"><span class="muted">Пользователей</span><b><?= $userCount ?></b></div>
    <div class="stat"><span class="muted">Курток в каталоге</span><b><?= $productCount ?></b></div>
    <div class="stat"><span class="muted">Заказов</span><b><?= $orderCount ?></b></div>
    <div class="stat"><span class="muted">Выручка</span><b><?= format_price($revenue) ?></b></div>
  </div>

  <h2 style="font-size:22px; margin-top:40px;">Пользователи</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Имя</th><th>Email</th><th>Заказов</th><th>Регистрация</th><th>Роль</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): $isSelf = (int) $u['id'] === (int) $currentUser['id']; ?>
          <tr>
            <td><b><?= e($u['name']) ?></b></td>
            <td><?= e($u['email']) ?></td>
            <td><?= (int) $u['order_count'] ?></td>
            <td><?= e(format_datetime($u['created_at'])) ?></td>
            <td>
              <?php if ($isSelf): ?>
                <span class="badge blue">admin (вы)</span>
              <?php else: ?>
                <form method="post" action="/admin/user_role.php" class="actions">
                  <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                  <select name="role" style="width:auto; padding:8px 10px;">
                    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>user</option>
                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                  </select>
                  <button type="submit" class="btn btn-light btn-sm">Сохранить</button>
                </form>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!$isSelf): ?>
                <form method="post" action="/admin/user_delete.php" onsubmit="return confirm('Удалить пользователя «<?= e($u['name']) ?>» вместе с его заказами?')">
                  <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
