<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$db = getDB();

$taskId = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;
$task = [
    'title' => '',
    'description' => '',
    'category_id' => null,
    'status' => 'todo',
    'priority' => 'medium',
    'due_date' => '',
];
$isEdit = false;

if ($taskId !== null) {
    $stmt = $db->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$taskId, $user['id']]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('error', 'Задача не найдена.');
        redirect('tasks.php');
    }
    $task = $found;
    $isEdit = true;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task['title'] = trim($_POST['title'] ?? '');
    $task['description'] = trim($_POST['description'] ?? '');
    $task['category_id'] = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
    $task['status'] = $_POST['status'] ?? 'todo';
    $task['priority'] = $_POST['priority'] ?? 'medium';
    $task['due_date'] = trim($_POST['due_date'] ?? '');

    if ($task['title'] === '' || mb_strlen($task['title']) < 3) {
        $errors[] = 'Название задачи должно содержать минимум 3 символа.';
    }
    if (!in_array($task['status'], ['todo', 'in_progress', 'done'], true)) {
        $errors[] = 'Некорректный статус.';
    }
    if (!in_array($task['priority'], ['low', 'medium', 'high'], true)) {
        $errors[] = 'Некорректный приоритет.';
    }
    if ($task['due_date'] !== '' && !DateTime::createFromFormat('Y-m-d', $task['due_date'])) {
        $errors[] = 'Некорректная дата срока.';
    }

    if (!$errors) {
        $dueDate = $task['due_date'] !== '' ? $task['due_date'] : null;

        if ($isEdit) {
            $stmt = $db->prepare(
                'UPDATE tasks SET title = ?, description = ?, category_id = ?, status = ?, priority = ?, due_date = ?
                 WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([
                $task['title'], $task['description'], $task['category_id'],
                $task['status'], $task['priority'], $dueDate,
                $taskId, $user['id'],
            ]);
            set_flash('success', 'Задача обновлена.');
        } else {
            $stmt = $db->prepare(
                'INSERT INTO tasks (user_id, category_id, title, description, status, priority, due_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $user['id'], $task['category_id'], $task['title'], $task['description'],
                $task['status'], $task['priority'], $dueDate,
            ]);
            set_flash('success', 'Задача создана.');
        }

        redirect('tasks.php');
    }
}

$categories = $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$pageTitle = $isEdit ? 'Редактирование задачи' : 'Новая задача';
require __DIR__ . '/includes/header.php';
?>

<section class="max-w-2xl mx-auto px-4 py-10">
  <h1 class="text-2xl font-bold text-slate-900 mb-6"><?= $isEdit ? 'Редактирование задачи' : 'Новая задача' ?></h1>

  <?php if ($errors): ?>
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
      <ul class="list-disc list-inside space-y-1">
        <?php foreach ($errors as $error): ?>
          <li><?= e($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" class="bg-white border border-slate-200 rounded-xl p-6 space-y-5">

    <div>
      <label for="title" class="block text-sm font-medium text-slate-700 mb-1">Название</label>
      <input type="text" id="title" name="title" value="<?= e($task['title']) ?>" required minlength="3"
             class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
    </div>

    <div>
      <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Описание</label>
      <textarea id="description" name="description" rows="4"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"><?= e($task['description']) ?></textarea>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1">Категория</label>
        <select id="category_id" name="category_id"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
          <option value="">Без категории</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= (string) $task['category_id'] === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="due_date" class="block text-sm font-medium text-slate-700 mb-1">Срок выполнения</label>
        <input type="date" id="due_date" name="due_date" value="<?= e($task['due_date'] ?? '') ?>"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Статус</label>
        <select id="status" name="status"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
          <?php foreach (['todo', 'in_progress', 'done'] as $s): ?>
            <option value="<?= $s ?>" <?= $task['status'] === $s ? 'selected' : '' ?>><?= status_label($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="priority" class="block text-sm font-medium text-slate-700 mb-1">Приоритет</label>
        <select id="priority" name="priority"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
          <?php foreach (['low', 'medium', 'high'] as $p): ?>
            <option value="<?= $p ?>" <?= $task['priority'] === $p ? 'selected' : '' ?>><?= priority_label($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="flex gap-3 pt-2">
      <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700">
        <?= $isEdit ? 'Сохранить изменения' : 'Создать задачу' ?>
      </button>
      <a href="tasks.php" class="px-5 py-2.5 rounded-lg bg-white border border-slate-200 text-slate-600 font-medium hover:bg-slate-50">Отмена</a>
    </div>
  </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
