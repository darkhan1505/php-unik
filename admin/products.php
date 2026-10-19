<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__ — папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции — он нужен каждой странице.
require_once __DIR__ . '/../includes/init.php';
// Пускает только администратора, остальных перенаправляет. Скрипт дальше не выполнится.
require_admin();

// query() — запрос без параметров. fetchAll() возвращает все строки: массив ассоциативных массивов ['name' => '...'].
$products = getDB()->query(
    'SELECT p.id, p.name, p.price, p.image_url, p.in_stock, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.id DESC'
)->fetchAll();

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Куртки';
// require вставляет файл прямо сюда (в отличие от require_once — каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
require __DIR__ . '/../includes/header.php';
?>

<section class="container section-sm">
  <div class="page-head">
    <h2>Куртки</h2>
    <a class="btn btn-sm" href="/admin/product_form.php">+ Добавить куртку</a>
  </div>
  <?php require __DIR__ . '/_nav.php'; ?>

  <div class="table-wrap">
    <table>
      <thead><tr><th></th><th>Название</th><th>Категория</th><th>Цена</th><th>Наличие</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><img src="<?= e($p['image_url']) ?>" alt=""></td>
            <td><b><?= e($p['name']) ?></b></td>
            <td><?= e($p['category_name'] ?? '—') ?></td>
            <td><?= format_price((int) $p['price']) ?></td>
            <td><span class="badge <?= $p['in_stock'] ? 'green' : 'red' ?>"><?= $p['in_stock'] ? 'В наличии' : 'Нет' ?></span></td>
            <td>
              <div class="actions">
                <a class="btn btn-light btn-sm" href="/admin/product_form.php?id=<?= (int) $p['id'] ?>">Изменить</a>
                <form method="post" action="/admin/product_delete.php" onsubmit="return confirm('Удалить куртку «<?= e($p['name']) ?>»?')">
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?>
          <tr><td colspan="6" class="muted">Курток пока нет.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
