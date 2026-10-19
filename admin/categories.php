<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__  папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции  он нужен каждой странице.
require_once __DIR__ . '/../includes/init.php';
// Пускает только администратора, остальных перенаправляет. Скрипт дальше не выполнится.
require_admin();

// getDB() возвращает объект PDO  соединение с PostgreSQL (см. config/database.php).
$db = getDB();
$errors = [];

// $_SERVER['REQUEST_METHOD']  HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    // ?? ''  значение по умолчанию, если ключа в $_POST нет. trim() убирает пробелы по краям.
    $name = trim($_POST['name'] ?? '');

    // mb_strlen()  длина строки с учётом UTF-8 (кириллица); обычный strlen считает байты.
    if (mb_strlen($name) < 2) {
        $errors[] = 'Название категории должно содержать минимум 2 символа.';
    } else {
        // prepare() + execute()  подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
        // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll()  все строки.
        $stmt = $db->prepare('SELECT id FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $errors[] = 'Такая категория уже существует.';
        }
    }

    if (!$errors) {
        $stmt = $db->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$name]);
        // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
        set_flash('success', 'Категория добавлена.');
        // redirect() отправляет заголовок Location и вызывает exit  код ниже не выполнится.
        redirect('/admin/categories.php');
    }
}

// query()  запрос без параметров. fetchAll() возвращает все строки: массив ассоциативных массивов ['name' => '...'].
$categories = $db->query(
    'SELECT c.id, c.name, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll();

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Категории';
// require вставляет файл прямо сюда (в отличие от require_once  каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
require __DIR__ . '/../includes/header.php';
?>

<section class="container section-sm">
  <h2>Категории</h2>
  <?php require __DIR__ . '/_nav.php'; ?>

  <?php if ($errors): ?>
    <ul class="errors"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
  <?php endif; ?>

  <form method="post" class="row" style="margin-top:24px; max-width:480px;">
    <input type="hidden" name="action" value="create">
    <input type="text" name="name" placeholder="Новая категория" required>
    <button type="submit" class="btn btn-sm" style="white-space:nowrap;">Добавить</button>
  </form>

  <div class="table-wrap">
    <table>
      <thead><tr><th>Название</th><th>Курток</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($categories as $c): ?>
          <tr>
            <td><b><?= e($c['name']) ?></b></td>
            <td><?= (int) $c['product_count'] ?></td>
            <td>
              <form method="post" action="/admin/category_delete.php" onsubmit="return confirm('Удалить категорию «<?= e($c['name']) ?>»? Куртки останутся без категории.')">
                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
