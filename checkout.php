<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__ — папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции — он нужен каждой странице.
require_once __DIR__ . '/includes/init.php';
// Если пользователь не вошёл — redirect() отправит на /login.php и остановит скрипт (exit).
require_login();

$cart = cart_items();
if (!$cart['items']) {
    // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
    set_flash('error', 'Корзина пуста.');
    // redirect() отправляет заголовок Location и вызывает exit — код ниже не выполнится.
    redirect('/cart.php');
}

$errors = [];
$fullName = current_user()['name'];
$phone = '';
$address = '';

// $_SERVER['REQUEST_METHOD'] — HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ?? '' — значение по умолчанию, если ключа в $_POST нет. trim() убирает пробелы по краям.
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // mb_strlen() — длина строки с учётом UTF-8 (кириллица); обычный strlen считает байты.
    if (mb_strlen($fullName) < 2) {
        $errors[] = 'Укажите имя получателя.';
    }
    // preg_match() — проверка строки регулярным выражением (true, если подходит).
    if (!preg_match('/^[0-9+()\-\s]{6,30}$/', $phone)) {
        $errors[] = 'Укажите корректный телефон.';
    }
    if (mb_strlen($address) < 5) {
        $errors[] = 'Укажите адрес доставки.';
    }

    if (!$errors) {
        // getDB() возвращает объект PDO — соединение с PostgreSQL (см. config/database.php).
        $db = getDB();
        // Транзакция: заказ и все его позиции запишутся вместе, а при ошибке rollBack() откатит всё.
        // RETURNING id — PostgreSQL вернёт id только что вставленной строки.
        $db->beginTransaction();
        // try/catch ловит исключения: PDO кидает их при ошибках SQL (включено ERRMODE_EXCEPTION в config/database.php).
        try {
            // prepare() + execute() — подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
            // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll() — все строки.
            $stmt = $db->prepare(
                'INSERT INTO orders (user_id, full_name, phone, address, total) VALUES (?, ?, ?, ?, ?) RETURNING id'
            );
            $stmt->execute([current_user()['id'], $fullName, $phone, $address, $cart['total']]);
            // fetchColumn() — значение первого столбца первой строки (удобно для COUNT/SUM).
            $orderId = (int) $stmt->fetchColumn();

            $itemStmt = $db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, size, quantity) VALUES (?, ?, ?, ?, ?, ?)'
            );
            // foreach ($массив as $элемент) — цикл по массиву.
            foreach ($cart['items'] as $item) {
                $itemStmt->execute([$orderId, $item['product_id'], $item['name'], $item['price'], $item['size'], $item['quantity']]);
            }
            $db->commit();
        } catch (Throwable $ex) {
            $db->rollBack();
            throw $ex;
        }

        // $_SESSION — массив, который PHP хранит на сервере между запросами (привязан к пользователю через cookie).
        unset($_SESSION['cart']);
        set_flash('success', 'Заказ №' . $orderId . ' оформлен. Спасибо за покупку!');
        redirect('/profile.php');
    }
}

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Оформление заказа';
// require вставляет файл прямо сюда (в отличие от require_once — каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
require __DIR__ . '/includes/header.php';
?>

<section class="container section-sm">
  <div class="grid" style="align-items:start;">
    <div class="box">
      <h1>Оформление заказа</h1>
      <p class="muted">Оплата при получении.</p>

      <?php if ($errors): ?>
        <ul class="errors">
          <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="field">
          <label for="full_name">Имя получателя</label>
          <input type="text" id="full_name" name="full_name" value="<?= e($fullName) ?>" required>
        </div>
        <div class="field">
          <label for="phone">Телефон</label>
          <input type="tel" id="phone" name="phone" value="<?= e($phone) ?>" placeholder="+7 700 000 00 00" required>
        </div>
        <div class="field">
          <label for="address">Адрес доставки</label>
          <input type="text" id="address" name="address" value="<?= e($address) ?>" required>
        </div>
        <button type="submit" class="btn btn-block">Подтвердить заказ</button>
      </form>
    </div>

    <div class="box">
      <h2 style="font-size:22px;">Ваш заказ</h2>
      <?php foreach ($cart['items'] as $item): ?>
        <p style="margin-top:14px;">
          <b><?= e($item['name']) ?></b>, размер <?= e($item['size']) ?> × <?= $item['quantity'] ?>
          <span style="float:right;"><?= format_price($item['sum']) ?></span>
        </p>
      <?php endforeach; ?>
      <div class="total">Итого: <?= format_price($cart['total']) ?></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
