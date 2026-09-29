<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$db = getDB();

$statusFilter = $_GET['status'] ?? '';
$allowedStatuses = ['todo', 'in_progress', 'done'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$sql = "SELECT t.id, t.title, t.status, t.priority, t.due_date, t.created_at,
               u.name AS user_name, c.name AS category_name
        FROM tasks t
        JOIN users u ON u.id = t.user_id
        LEFT JOIN categories c ON c.id = t.category_id";
$params = [];
if ($statusFilter !== '') {
    $sql .= ' WHERE t.status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY t.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$pageTitle = 'Все задачи';
require __DIR__ . '/../includes/header.php';
?>

<section class="max-w-6xl mx-auto px-4 py-10">
  <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Все задачи (все пользователи)</h1>
    <a href="index.php" class="text-sm font-medium text-brand-600 hover:underline">← Назад в админ-панель</a>
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
  </form>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <?php if (!$tasks): ?>
      <div class="p-10 text-center text-slate-500">Задачи не найдены.</div>
    <?php else: ?>
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
          <tr>
            <th class="px-5 py-3 font-medium">Название</th>
            <th class="px-5 py-3 font-medium">Автор</th>
            <th class="px-5 py-3 font-medium">Категория</th>
            <th class="px-5 py-3 font-medium">Приоритет</th>
            <th class="px-5 py-3 font-medium">Статус</th>
            <th class="px-5 py-3 font-medium">Создана</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($tasks as $task): ?>
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3 font-medium text-slate-800"><?= e($task['title']) ?></td>
              <td class="px-5 py-3 text-slate-500"><?= e($task['user_name']) ?></td>
              <td class="px-5 py-3 text-slate-500"><?= e($task['category_name'] ?? '') ?></td>
              <td class="px-5 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium <?= priority_badge_class($task['priority']) ?>"><?= priority_label($task['priority']) ?></span>
              </td>
              <td class="px-5 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium <?= status_badge_class($task['status']) ?>"><?= status_label($task['status']) ?></span>
              </td>
              <td class="px-5 py-3 text-slate-500"><?= format_date($task['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
