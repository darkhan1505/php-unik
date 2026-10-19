<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__ — папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции — он нужен каждой странице.
require_once __DIR__ . '/includes/init.php';

// $_SERVER['REQUEST_METHOD'] — HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        // $_GET — параметры из URL (?id=5), $_POST — поля отправленной формы. ctype_digit() — строка состоит только из цифр,
        // (int) приводит к числу. Запись «условие ? а : б» — тернарный оператор (как if/else в одну строку).
        $productId = isset($_POST['product_id']) && ctype_digit($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        // ?? '' — значение по умолчанию, если ключа в $_POST нет. trim() убирает пробелы по краям.
        $size = trim($_POST['size'] ?? '');

        // prepare() + execute() — подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
        // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll() — все строки.
        $stmt = getDB()->prepare('SELECT sizes, in_stock FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        // in_array($значение, $массив, true) — есть ли значение в массиве; true = строгое сравнение типов.
        if (!$product || !$product['in_stock'] || !in_array($size, parse_sizes($product['sizes']), true)) {
            // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
            set_flash('error', 'Не удалось добавить куртку в корзину.');
            // redirect() отправляет заголовок Location и вызывает exit — код ниже не выполнится.
            redirect('/index.php');
        }

        $key = $productId . ':' . $size;
        // $_SESSION — массив, который PHP хранит на сервере между запросами (привязан к пользователю через cookie).
        $_SESSION['cart'][$key] = min(10, ($_SESSION['cart'][$key] ?? 0) + 1);
        set_flash('success', 'Куртка добавлена в корзину.');
        redirect('/cart.php');
    }

    if ($action === 'update') {
        $key = (string) ($_POST['key'] ?? '');
        $qty = (int) ($_POST['quantity'] ?? 0);
        if (isset($_SESSION['cart'][$key])) {
            if ($qty < 1) {
                // unset() удаляет переменную или элемент массива.
                unset($_SESSION['cart'][$key]);
            } else {
                // min() — меньшее из двух чисел: ограничиваем количество десятью.
                $_SESSION['cart'][$key] = min(10, $qty);
            }
        }
        redirect('/cart.php');
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][(string) ($_POST['key'] ?? '')]);
        redirect('/cart.php');
    }

    redirect('/cart.php');
}

$cart = cart_items();

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Корзина';
// require вставляет файл прямо сюда (в отличие от require_once — каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
require __DIR__ . '/includes/header.php';
?>

<section class="container section-sm">
  <h2>Корзина</h2>

  <?php if (!$cart['items']): ?>
    <p class="muted" style="margin:24px 0;">В корзине пока пусто.</p>
    <a class="btn" href="/index.php#catalog">Перейти в каталог</a>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th></th><th>Куртка</th><th>Размер</th><th>Цена</th><th>Кол-во</th><th>Сумма</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($cart['items'] as $item): ?>
            <tr>
              <td><img src="<?= e($item['image_url']) ?>" alt=""></td>
              <td><a href="/product.php?id=<?= $item['product_id'] ?>"><b><?= e($item['name']) ?></b></a></td>
              <td><?= e($item['size']) ?></td>
              <td><?= format_price($item['price']) ?></td>
              <td>
                <form method="post" class="actions">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                  <input type="number" name="quantity" class="qty" min="1" max="10" value="<?= $item['quantity'] ?>">
                  <button type="submit" class="btn btn-light btn-sm">ОК</button>
                </form>
              </td>
              <td><b><?= format_price($item['sum']) ?></b></td>
              <td>
                <form method="post">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="key" value="<?= e($item['key']) ?>">
                  <button type="submit" class="btn btn-light btn-sm">Удалить</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="total">Итого: <?= format_price($cart['total']) ?></div>
    <div style="text-align:right;">
      <a class="btn" href="/checkout.php">Оформить заказ</a>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
