<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$db = getDB();
$user = current_user();
$errors = [];
$passwordErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || mb_strlen($name) < 2) {
        $errors[] = 'Имя должно содержать минимум 2 символа.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }

    if (!$errors) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $errors[] = 'Этот email уже используется другим пользователем.';
        }
    }

    if (!$errors) {
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$name, $email, $user['id']]);
        set_flash('success', 'Профиль обновлён.');
        redirect('profile.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'password') {
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['new_password_confirm'] ?? '');

    $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        $passwordErrors[] = 'Текущий пароль указан неверно.';
    }
    if (mb_strlen($new) < 6) {
        $passwordErrors[] = 'Новый пароль должен содержать минимум 6 символов.';
    }
    if ($new !== $confirm) {
        $passwordErrors[] = 'Новые пароли не совпадают.';
    }

    if (!$passwordErrors) {
        $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        set_flash('success', 'Пароль успешно изменён.');
        redirect('profile.php');
    }
}

$user = current_user();
$pageTitle = 'Профиль';
require __DIR__ . '/includes/header.php';
?>

<section class="max-w-2xl mx-auto px-4 py-10 space-y-8">
  <h1 class="text-2xl font-bold text-slate-900">Профиль</h1>

  <div class="bg-white border border-slate-200 rounded-xl p-6">
    <h2 class="font-semibold text-slate-800 mb-4">Личные данные</h2>

    <?php if ($errors): ?>
      <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <input type="hidden" name="form" value="profile">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Имя</label>
        <input type="text" name="name" value="<?= e($user['name']) ?>" required minlength="2"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
        <input type="email" name="email" value="<?= e($user['email']) ?>" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>
      <div class="text-sm text-slate-500">Роль: <span class="font-medium text-slate-700"><?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?></span></div>
      <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700">Сохранить</button>
    </form>
  </div>

  <div class="bg-white border border-slate-200 rounded-xl p-6">
    <h2 class="font-semibold text-slate-800 mb-4">Смена пароля</h2>

    <?php if ($passwordErrors): ?>
      <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($passwordErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" data-password-match-form novalidate class="space-y-4">
      <input type="hidden" name="form" value="password">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Текущий пароль</label>
        <input type="password" name="current_password" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Новый пароль</label>
        <input type="password" name="new_password" required minlength="6" data-password
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Повторите новый пароль</label>
        <input type="password" name="new_password_confirm" required minlength="6" data-password-confirm
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        <p data-password-hint class="hidden text-xs text-red-600 mt-1">Пароли не совпадают</p>
      </div>
      <button type="submit" class="px-5 py-2.5 rounded-lg bg-slate-800 text-white font-semibold hover:bg-slate-900">Изменить пароль</button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
