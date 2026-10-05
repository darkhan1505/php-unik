<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = isset($_POST['product_id']) && ctype_digit($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $size = trim($_POST['size'] ?? '');

        $stmt = getDB()->prepare('SELECT sizes, in_stock FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product || !$product['in_stock'] || !in_array($size, parse_sizes($product['sizes']), true)) {
            set_flash('error', 'Не удалось добавить куртку в корзину.');
            redirect('/index.php');
        }

        $key = $productId . ':' . $size;
        $_SESSION['cart'][$key] = min(10, ($_SESSION['cart'][$key] ?? 0) + 1);
        set_flash('success', 'Куртка добавлена в корзину.');
        redirect('/cart.php');
    }

    if ($action === 'update') {
        $key = (string) ($_POST['key'] ?? '');
        $qty = (int) ($_POST['quantity'] ?? 0);
        if (isset($_SESSION['cart'][$key])) {
            if ($qty < 1) {
                unset($_SESSION['cart'][$key]);
            } else {
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

$pageTitle = 'Корзина';
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
