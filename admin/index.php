<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$db = getDB();
$currentUser = current_user();

$userCount = (int) $db->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'];
$taskCount = (int) $db->query('SELECT COUNT(*) AS c FROM tasks')->fetch()['c'];
$categoryCount = (int) $db->query('SELECT COUNT(*) AS c FROM categories')->fetch()['c'];

$users = $db->query(
    "SELECT u.id, u.name, u.email, u.role, u.created_at, COUNT(t.id) AS task_count
     FROM users u
     LEFT JOIN tasks t ON t.user_id = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC"
)->fetchAll();

$pageTitle = 'Админ-панель';
require __DIR__ . '/../includes/header.php';
?>

<section class="max-w-6xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Админ-панель</h1>
    <div class="flex gap-2 text-sm">
      <a href="tasks.php" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50">Все задачи</a>
      <a href="categories.php" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50">Категории</a>
    </div>
  </div>

  <div class="grid sm:grid-cols-3 gap-4 mb-10">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
      <p class="text-sm text-slate-500">Пользователей</p>
      <p class="text-3xl font-bold text-slate-900 mt-1"><?= $userCount ?></p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
      <p class="text-sm text-slate-500">Задач всего</p>
      <p class="text-3xl font-bold text-slate-900 mt-1"><?= $taskCount ?></p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5">
      <p class="text-sm text-slate-500">Категорий</p>
      <p class="text-3xl font-bold text-slate-900 mt-1"><?= $categoryCount ?></p>
    </div>
  </div>

  <h2 class="text-lg font-semibold text-slate-800 mb-4">Пользователи</h2>
  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500 text-left">
        <tr>
          <th class="px-5 py-3 font-medium">Имя</th>
          <th class="px-5 py-3 font-medium">Email</th>
          <th class="px-5 py-3 font-medium">Роль</th>
          <th class="px-5 py-3 font-medium">Задач</th>
          <th class="px-5 py-3 font-medium">Регистрация</th>
          <th class="px-5 py-3 font-medium text-right">Действия</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($users as $u): ?>
          <tr class="hover:bg-slate-50">
            <td class="px-5 py-3 font-medium text-slate-800"><?= e($u['name']) ?></td>
            <td class="px-5 py-3 text-slate-500"><?= e($u['email']) ?></td>
            <td class="px-5 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' ?>">
                <?= $u['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?>
              </span>
            </td>
            <td class="px-5 py-3 text-slate-500"><?= (int) $u['task_count'] ?></td>
            <td class="px-5 py-3 text-slate-500"><?= format_date($u['created_at']) ?></td>
            <td class="px-5 py-3">
              <div class="flex justify-end gap-3">
                <?php if ((int) $u['id'] !== (int) $currentUser['id']): ?>
                  <form method="post" action="user_role.php">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                    <button type="submit" class="text-brand-600 hover:underline">
                      <?= $u['role'] === 'admin' ? 'Забрать права админа' : 'Сделать админом' ?>
                    </button>
                  </form>
                  <form method="post" action="user_delete.php" class="js-confirm-delete" data-confirm-message="Удалить пользователя «<?= e($u['name']) ?>»? Все его задачи будут удалены безвозвратно.">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="text-red-600 hover:underline">Удалить</button>
                  </form>
                <?php else: ?>
                  <span class="text-slate-400">Это вы</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
