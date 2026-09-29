<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$db = getDB();

$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$allowedStatuses = ['todo', 'in_progress', 'done'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$sql = 'SELECT t.id, t.title, t.status, t.priority, t.due_date, c.name AS category_name
        FROM tasks t
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.user_id = :user_id';
$params = ['user_id' => $user['id']];

if ($statusFilter !== '') {
    $sql .= ' AND t.status = :status';
    $params['status'] = $statusFilter;
}
if ($categoryFilter !== '' && ctype_digit((string) $categoryFilter)) {
    $sql .= ' AND t.category_id = :category_id';
    $params['category_id'] = (int) $categoryFilter;
}

$sql .= ' ORDER BY t.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$categories = $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Мои задачи';
require __DIR__ . '/includes/header.php';
?>

<section class="max-w-6xl mx-auto px-4 py-10">
  <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Мои задачи</h1>
    <a href="task_form.php" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">+ Новая задача</a>
  </div>

  <form method="get" class="flex flex-wrap gap-3 mb-6 bg-white border border-slate-200 rounded-xl p-4">
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Статус</label>
      <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
        <option value="">Все статусы</option>
        <?php foreach ($allowedStatuses as $s): ?>
          <option value="<?= e($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= status_label($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Категория</label>
      <select name="category" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
        <option value="">Все категории</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int) $cat['id'] ?>" <?= (string) $categoryFilter === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if ($statusFilter !== '' || $categoryFilter !== ''): ?>
      <div class="flex items-end">
        <a href="tasks.php" class="text-sm text-slate-500 hover:text-brand-600 underline">Сбросить фильтры</a>
      </div>
    <?php endif; ?>
  </form>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <?php if (!$tasks): ?>
      <div class="p-10 text-center text-slate-500">
        Задачи не найдены. <a href="task_form.php" class="text-brand-600 font-medium hover:underline">Создать задачу</a>
      </div>
    <?php else: ?>
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
          <tr>
            <th class="px-5 py-3 font-medium">Название</th>
            <th class="px-5 py-3 font-medium">Категория</th>
            <th class="px-5 py-3 font-medium">Приоритет</th>
            <th class="px-5 py-3 font-medium">Статус</th>
            <th class="px-5 py-3 font-medium">Срок</th>
            <th class="px-5 py-3 font-medium text-right">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($tasks as $task): ?>
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3 font-medium text-slate-800"><?= e($task['title']) ?></td>
              <td class="px-5 py-3 text-slate-500"><?= e($task['category_name'] ?? '') ?></td>
              <td class="px-5 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium <?= priority_badge_class($task['priority']) ?>"><?= priority_label($task['priority']) ?></span>
              </td>
              <td class="px-5 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium <?= status_badge_class($task['status']) ?>"><?= status_label($task['status']) ?></span>
              </td>
              <td class="px-5 py-3 text-slate-500"><?= format_date($task['due_date']) ?></td>
              <td class="px-5 py-3">
                <div class="flex justify-end gap-3">
                  <a href="task_form.php?id=<?= (int) $task['id'] ?>" class="text-brand-600 hover:underline">Изменить</a>
                  <form method="post" action="task_delete.php" class="js-confirm-delete" data-confirm-message="Удалить задачу «<?= e($task['title']) ?>»? Это действие необратимо.">
                    <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                    <button type="submit" class="text-red-600 hover:underline">Удалить</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
