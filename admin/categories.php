<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['name'] ?? '');

    if (mb_strlen($name) < 2) {
        $errors[] = 'Название категории должно содержать минимум 2 символа.';
    } else {
        $stmt = $db->prepare('SELECT id FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $errors[] = 'Такая категория уже существует.';
        }
    }

    if (!$errors) {
        $stmt = $db->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$name]);
        set_flash('success', 'Категория добавлена.');
        redirect('/admin/categories.php');
    }
}

$categories = $db->query(
    'SELECT c.id, c.name, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll();

$pageTitle = 'Категории';
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
              <form method="post" action="/admin/category_delete.php" class="js-confirm-delete"
                    data-confirm-message="Удалить категорию «<?= e($c['name']) ?>»? Куртки останутся без категории.">
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
