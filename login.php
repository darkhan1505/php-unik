<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Введите email и пароль.';
    } else {
        $stmt = getDB()->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Неверный email или пароль.';
        } else {
            $_SESSION['user_id'] = (int) $user['id'];
            session_regenerate_id(true);
            set_flash('success', 'С возвращением, ' . $user['name'] . '!');
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Вход';
require __DIR__ . '/includes/header.php';
?>

<section class="max-w-md mx-auto px-4 py-12">
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-1">Вход в аккаунт</h1>
    <p class="text-sm text-slate-500 mb-6">Рады видеть вас снова.</p>

    <?php if ($errors): ?>
      <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate class="space-y-4">

      <div>
        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Пароль</label>
        <div class="flex gap-2">
          <input type="password" id="password" name="password" required
                 class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
          <button type="button" data-toggle-password="password" class="px-3 rounded-lg border border-slate-300 text-xs text-slate-600 hover:bg-slate-50 whitespace-nowrap">Показать</button>
        </div>
      </div>

      <button type="submit" class="w-full py-2.5 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700">
        Войти
      </button>
    </form>

    <p class="text-sm text-slate-500 mt-6 text-center">
      Нет аккаунта? <a href="register.php" class="text-brand-600 font-medium hover:underline">Зарегистрироваться</a>
    </p>
    <p class="text-xs text-slate-400 mt-4 text-center">
      Демо-администратор: admin@taskflow.local / admin123
    </p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
