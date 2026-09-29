<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? 'slate');

    if ($name === '' || mb_strlen($name) < 2) {
        $errors[] = 'Название категории должно содержать минимум 2 символа.';
    } else {
        $stmt = $db->prepare('SELECT id FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $errors[] = 'Такая категория уже существует.';
        }
    }

    if (!$errors) {
        $stmt = $db->prepare('INSERT INTO categories (name, color) VALUES (?, ?)');
        $stmt->execute([$name, $color]);
        set_flash('success', 'Категория добавлена.');
        redirect('categories.php');
    }
}

$categories = $db->query(
    'SELECT c.id, c.name, c.color, COUNT(t.id) AS task_count
     FROM categories c
     LEFT JOIN tasks t ON t.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll();

$colorOptions = ['slate', 'blue', 'purple', 'green', 'amber', 'red'];

$pageTitle = 'Категории';
require __DIR__ . '/../includes/header.php';
?>

<section class="max-w-3xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Категории задач</h1>
    <a href="index.php" class="text-sm font-medium text-brand-600 hover:underline">← Назад в админ-панель</a>
  </div>

  <?php if ($errors): ?>
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
      <ul class="list-disc list-inside space-y-1">
        <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" class="bg-white border border-slate-200 rounded-xl p-5 mb-6 flex flex-wrap items-end gap-3">
    <input type="hidden" name="action" value="create">
    <div class="flex-1 min-w-[180px]">
      <label class="block text-xs font-medium text-slate-500 mb-1">Название</label>
      <input type="text" name="name" required minlength="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Цвет</label>
      <select name="color" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <?php foreach ($colorOptions as $c): ?>
          <option value="<?= $c ?>"><?= ucfirst($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">Добавить</button>
  </form>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500 text-left">
        <tr>
          <th class="px-5 py-3 font-medium">Название</th>
          <th class="px-5 py-3 font-medium">Цвет</th>
          <th class="px-5 py-3 font-medium">Задач</th>
          <th class="px-5 py-3 font-medium text-right">Действия</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($categories as $cat): ?>
          <tr class="hover:bg-slate-50">
            <td class="px-5 py-3 font-medium text-slate-800"><?= e($cat['name']) ?></td>
            <td class="px-5 py-3 text-slate-500"><?= e($cat['color']) ?></td>
            <td class="px-5 py-3 text-slate-500"><?= (int) $cat['task_count'] ?></td>
            <td class="px-5 py-3 text-right">
              <form method="post" action="category_delete.php" class="js-confirm-delete inline" data-confirm-message="Удалить категорию «<?= e($cat['name']) ?>»? У связанных задач категория будет очищена.">
                <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                <button type="submit" class="text-red-600 hover:underline">Удалить</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
