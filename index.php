<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__  папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции  он нужен каждой странице.
require_once __DIR__ . '/includes/init.php';

// getDB() возвращает объект PDO  соединение с PostgreSQL (см. config/database.php).
$db = getDB();
// query()  запрос без параметров. fetchAll() возвращает все строки: массив ассоциативных массивов ['name' => '...'].
$categories = $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// $_GET  параметры из URL (?id=5), $_POST  поля отправленной формы. ctype_digit()  строка состоит только из цифр,
// (int) приводит к числу. Запись «условие ? а : б»  тернарный оператор (как if/else в одну строку).
$categoryId = isset($_GET['category']) && ctype_digit($_GET['category']) ? (int) $_GET['category'] : null;

$sql = 'SELECT id, name, subtitle, price, image_url, in_stock FROM products';
$params = [];
if ($categoryId !== null) {
    $sql .= ' WHERE category_id = ?';
    $params[] = $categoryId;
}
$sql .= ' ORDER BY id';
// prepare() + execute()  подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
// fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll()  все строки.
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$heroImage = $products[0]['image_url'] ?? 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=900&q=85';

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Магазин курток';
// require вставляет файл прямо сюда (в отличие от require_once  каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
require __DIR__ . '/includes/header.php';
?>

<section class="hero" id="home">
  <div class="container grid">
    <div>
      <span class="eyebrow">Осенняя коллекция</span>
      <h1>Тепло.<br>Стильно.<br>Каждый день.</h1>
      <p>Куртки для прохладных утр и твоего привычного ритма.</p>
      <a class="btn" href="#catalog">Смотреть куртки</a>
    </div>
    <img src="<?= e($heroImage) ?>" alt="Куртка">
  </div>
</section>

<section id="catalog" class="container section">
  <h2>Выбери свою куртку</h2>

  <div class="filters">
    <a href="/index.php#catalog" class="chip <?= $categoryId === null ? 'active' : '' ?>">Все</a>
    <?php foreach ($categories as $c): ?>
      <a href="/index.php?category=<?= (int) $c['id'] ?>#catalog"
         class="chip <?= $categoryId === (int) $c['id'] ? 'active' : '' ?>"><?= e($c['name']) ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (!$products): ?>
    <p class="muted" style="margin-top:30px;">В этой категории пока нет курток.</p>
  <?php else: ?>
    <div class="products">
      <?php foreach ($products as $p): ?>
        <a class="card" href="/product.php?id=<?= (int) $p['id'] ?>">
          <div class="thumb"><img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>"></div>
          <div class="card-body">
            <h3><?= e($p['name']) ?></h3>
            <p><?= e($p['subtitle']) ?></p>
            <?php if ($p['in_stock']): ?>
              <span class="price"><?= format_price((int) $p['price']) ?></span>
            <?php else: ?>
              <span class="badge red">Нет в наличии</span>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
