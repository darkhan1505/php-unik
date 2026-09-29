<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$db = getDB();

$stmt = $db->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) AS todo,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done
     FROM tasks WHERE user_id = ?"
);
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();

$stmt = $db->prepare(
    'SELECT t.id, t.title, t.status, t.priority, t.due_date, c.name AS category_name
     FROM tasks t
     LEFT JOIN categories c ON c.id = t.category_id
     WHERE t.user_id = ?
     ORDER BY t.created_at DESC
     LIMIT 5'
);
$stmt->execute([$user['id']]);
$recentTasks = $stmt->fetchAll();

$pageTitle = 'Дашборд';
require __DIR__ . '/includes/header.php';
?>

<section class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-2xl font-bold text-slate-900 mb-1">Привет, <?= e($user['name']) ?>!</h1>
  <p class="text-slate-500 mb-8">Вот краткая сводка по вашим задачам.</p>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
      <p class="text-sm text-slate-500">Всего задач</p>
      <p class="text-3xl font-bold text-slate-900 mt-1"><?= (int) ($stats['total'] ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
      <p class="text-sm text-slate-500">К выполнению</p>
      <p class="text-3xl font-bold text-slate-700 mt-1"><?= (int) ($stats['todo'] ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
      <p class="text-sm text-slate-500">В процессе</p>
      <p class="text-3xl font-bold text-amber-600 mt-1"><?= (int) ($stats['in_progress'] ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
      <p class="text-sm text-slate-500">Готово</p>
      <p class="text-3xl font-bold text-green-600 mt-1"><?= (int) ($stats['done'] ?? 0) ?></p>
    </div>
  </div>

  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-slate-800">Последние задачи</h2>
    <a href="tasks.php" class="text-sm font-medium text-brand-600 hover:underline">Все задачи →</a>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <?php if (!$recentTasks): ?>
      <div class="p-8 text-center text-slate-500">
        <p class="mb-4">У вас пока нет задач.</p>
        <a href="task_form.php" class="inline-block px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">Создать первую задачу</a>
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
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($recentTasks as $task): ?>
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-3">
                <a href="task_form.php?id=<?= (int) $task['id'] ?>" class="font-medium text-slate-800 hover:text-brand-600"><?= e($task['title']) ?></a>
              </td>
              <td class="px-5 py-3 text-slate-500"><?= e($task['category_name'] ?? '') ?></td>
              <td class="px-5 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium <?= priority_badge_class($task['priority']) ?>"><?= priority_label($task['priority']) ?></span>
              </td>
              <td class="px-5 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium <?= status_badge_class($task['status']) ?>"><?= status_label($task['status']) ?></span>
              </td>
              <td class="px-5 py-3 text-slate-500"><?= format_date($task['due_date']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
