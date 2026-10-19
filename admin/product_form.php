<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__ — папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции — он нужен каждой странице.
require_once __DIR__ . '/../includes/init.php';
// Пускает только администратора, остальных перенаправляет. Скрипт дальше не выполнится.
require_admin();

// getDB() возвращает объект PDO — соединение с PostgreSQL (см. config/database.php).
$db = getDB();
// $_GET — параметры из URL (?id=5), $_POST — поля отправленной формы. ctype_digit() — строка состоит только из цифр,
// (int) приводит к числу. Запись «условие ? а : б» — тернарный оператор (как if/else в одну строку).
$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;

$product = [
    'name' => '', 'subtitle' => '', 'description' => '', 'price' => '',
    'image_url' => '', 'sizes' => 'S,M,L,XL', 'in_stock' => true, 'category_id' => '',
];

if ($id !== null) {
    // prepare() + execute() — подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
    // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll() — все строки.
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
        set_flash('error', 'Куртка не найдена.');
        // redirect() отправляет заголовок Location и вызывает exit — код ниже не выполнится.
        redirect('/admin/products.php');
    }
    $product = $found;
}

// query() — запрос без параметров. fetchAll() возвращает все строки: массив ассоциативных массивов ['name' => '...'].
$categories = $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];

// $_SERVER['REQUEST_METHOD'] — HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ?? '' — значение по умолчанию, если ключа в $_POST нет. trim() убирает пробелы по краям.
    $product['name'] = trim($_POST['name'] ?? '');
    $product['subtitle'] = trim($_POST['subtitle'] ?? '');
    $product['description'] = trim($_POST['description'] ?? '');
    $product['price'] = trim($_POST['price'] ?? '');
    $product['image_url'] = trim($_POST['image_url'] ?? '');
    $product['sizes'] = implode(',', parse_sizes($_POST['sizes'] ?? ''));
    $product['in_stock'] = isset($_POST['in_stock']);
    $product['category_id'] = ctype_digit($_POST['category_id'] ?? '') ? (int) $_POST['category_id'] : '';

    // mb_strlen() — длина строки с учётом UTF-8 (кириллица); обычный strlen считает байты.
    if (mb_strlen($product['name']) < 2) {
        $errors[] = 'Название должно содержать минимум 2 символа.';
    }
    if (!ctype_digit($product['price'])) {
        $errors[] = 'Цена должна быть целым неотрицательным числом.';
    }
    // preg_match() — проверка строки регулярным выражением (true, если подходит).
    if ($product['image_url'] !== '' && !preg_match('#^(https?://|/)#i', $product['image_url'])) {
        $errors[] = 'Ссылка на фото должна начинаться с http://, https:// или /.';
    }
    if ($product['sizes'] === '') {
        $errors[] = 'Укажите хотя бы один размер (через запятую).';
    }

    if (!$errors) {
        $values = [
            $product['category_id'] === '' ? null : $product['category_id'],
            $product['name'], $product['subtitle'], $product['description'],
            (int) $product['price'], $product['image_url'], $product['sizes'],
            $product['in_stock'] ? 'true' : 'false',
        ];
        if ($id === null) {
            $stmt = $db->prepare(
                'INSERT INTO products (category_id, name, subtitle, description, price, image_url, sizes, in_stock)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute($values);
            set_flash('success', 'Куртка добавлена.');
        } else {
            $stmt = $db->prepare(
                'UPDATE products SET category_id = ?, name = ?, subtitle = ?, description = ?,
                 price = ?, image_url = ?, sizes = ?, in_stock = ? WHERE id = ?'
            );
            // Три точки (spread) распаковывают массив: получится $values + $id в конце.
            $stmt->execute([...$values, $id]);
            set_flash('success', 'Куртка обновлена.');
        }
        redirect('/admin/products.php');
    }
}

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = $id === null ? 'Новая куртка' : 'Редактирование куртки';
// require вставляет файл прямо сюда (в отличие от require_once — каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
require __DIR__ . '/../includes/header.php';
?>

<section class="narrow" style="width:min(640px, 90%);">
  <div class="box">
    <h1><?= e($pageTitle) ?></h1>

    <?php if ($errors): ?>
      <ul class="errors"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="field">
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="<?= e($product['name']) ?>" required>
      </div>
      <div class="field">
        <label for="subtitle">Краткое описание (под названием)</label>
        <input type="text" id="subtitle" name="subtitle" value="<?= e($product['subtitle']) ?>" placeholder="Кожаная куртка · Чёрный">
      </div>
      <div class="field">
        <label for="description">Описание</label>
        <textarea id="description" name="description" rows="4"><?= e($product['description']) ?></textarea>
      </div>
      <div class="field">
        <label for="category_id">Категория</label>
        <select id="category_id" name="category_id">
          <option value="">— без категории —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (string) $product['category_id'] === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="price">Цена, ₸</label>
        <input type="number" id="price" name="price" min="0" step="1" value="<?= e((string) $product['price']) ?>" required>
      </div>
      <div class="field">
        <label for="image_url">Ссылка на фото</label>
        <input type="text" id="image_url" name="image_url" value="<?= e($product['image_url']) ?>" placeholder="https://...">
      </div>
      <div class="field">
        <label for="sizes">Размеры (через запятую)</label>
        <input type="text" id="sizes" name="sizes" value="<?= e($product['sizes']) ?>" required>
      </div>
      <div class="field check">
        <input type="checkbox" id="in_stock" name="in_stock" <?= $product['in_stock'] ? 'checked' : '' ?>>
        <label for="in_stock" style="margin:0;">В наличии</label>
      </div>
      <div class="actions" style="margin-top:24px;">
        <button type="submit" class="btn" style="margin-top:0;">Сохранить</button>
        <a class="btn btn-light" style="margin-top:0;" href="/admin/products.php">Отмена</a>
      </div>
    </form>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
