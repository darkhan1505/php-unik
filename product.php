<?php
require_once __DIR__ . '/includes/init.php';

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = getDB()->prepare(
    'SELECT p.*, c.name AS category_name FROM products p
     LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    set_flash('error', 'Куртка не найдена.');
    redirect('/index.php');
}

$sizes = parse_sizes($product['sizes']);

$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>

<section class="container section-sm product">
  <div class="grid">
    <img class="product-img" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
    <div>
      <?php if ($product['category_name']): ?>
        <span class="eyebrow"><?= e($product['category_name']) ?></span>
      <?php endif; ?>
      <h1><?= e($product['name']) ?></h1>
      <p class="muted"><?= e($product['subtitle']) ?></p>
      <div class="price-big"><?= format_price((int) $product['price']) ?></div>
      <p class="muted" style="line-height:1.6;"><?= nl2br(e($product['description'])) ?></p>

      <?php if ($product['in_stock'] && $sizes): ?>
        <form method="post" action="/cart.php" style="margin-top:24px;">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
          <b>Размер</b>
          <div class="sizes">
            <?php foreach ($sizes as $i => $size): ?>
              <input type="radio" name="size" id="size-<?= e($size) ?>" value="<?= e($size) ?>" <?= $i === 0 ? 'checked' : '' ?>>
              <label for="size-<?= e($size) ?>"><?= e($size) ?></label>
            <?php endforeach; ?>
          </div>
          <button type="submit" class="btn">Добавить в корзину</button>
        </form>
      <?php else: ?>
        <p style="margin-top:24px;"><span class="badge red">Нет в наличии</span></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
