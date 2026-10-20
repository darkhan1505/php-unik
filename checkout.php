<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$cart = cart_items();
if (!$cart['items']) {
    set_flash('error', 'Корзина пуста.');
    redirect('/cart.php');
}

$errors = [];
$fullName = current_user()['name'];
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (mb_strlen($fullName) < 2) {
        $errors[] = 'Укажите имя получателя.';
    }
    if (!preg_match('/^[0-9+()\-\s]{6,30}$/', $phone)) {
        $errors[] = 'Укажите корректный телефон.';
    }
    if (mb_strlen($address) < 5) {
        $errors[] = 'Укажите адрес доставки.';
    }

    if (!$errors) {
        $db = getDB();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                'INSERT INTO orders (user_id, full_name, phone, address, total) VALUES (?, ?, ?, ?, ?) RETURNING id'
            );
            $stmt->execute([current_user()['id'], $fullName, $phone, $address, $cart['total']]);
            $orderId = (int) $stmt->fetchColumn();

            $itemStmt = $db->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, size, quantity) VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($cart['items'] as $item) {
                $itemStmt->execute([$orderId, $item['product_id'], $item['name'], $item['price'], $item['size'], $item['quantity']]);
            }
            $db->commit();
        } catch (Throwable $ex) {
            $db->rollBack();
            throw $ex;
        }

        unset($_SESSION['cart']);
        set_flash('success', 'Заказ №' . $orderId . ' оформлен. Спасибо за покупку!');
        redirect('/profile.php');
    }
}

$pageTitle = 'Оформление заказа';
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
