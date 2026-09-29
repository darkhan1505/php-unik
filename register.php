<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if ($name === '' || mb_strlen($name) < 2) {
        $errors[] = 'Имя должно содержать минимум 2 символа.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    if (mb_strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать минимум 6 символов.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (!$errors) {
        $stmt = getDB()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким email уже зарегистрирован.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = getDB()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $hash, 'user']);

        $_SESSION['user_id'] = (int) getDB()->lastInsertId();
        session_regenerate_id(true);

        set_flash('success', 'Регистрация прошла успешно! Добро пожаловать, ' . $name . '.');
        redirect('dashboard.php');
    }
}

$pageTitle = 'Регистрация';
require __DIR__ . '/includes/header.php';
?>

<section class="max-w-md mx-auto px-4 py-12">
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-1">Создать аккаунт</h1>
    <p class="text-sm text-slate-500 mb-6">Это бесплатно и займёт меньше минуты.</p>

    <?php if ($errors): ?>
      <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" data-password-match-form novalidate class="space-y-4">

      <div>
        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Имя</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" required minlength="2"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Пароль</label>
        <div class="flex gap-2">
          <input type="password" id="password" name="password" required minlength="6" data-password
                 class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
          <button type="button" data-toggle-password="password" class="px-3 rounded-lg border border-slate-300 text-xs text-slate-600 hover:bg-slate-50 whitespace-nowrap">Показать</button>
        </div>
      </div>

      <div>
        <label for="password_confirm" class="block text-sm font-medium text-slate-700 mb-1">Повторите пароль</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="6" data-password-confirm
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        <p data-password-hint class="hidden text-xs text-red-600 mt-1">Пароли не совпадают</p>
      </div>

      <button type="submit" class="w-full py-2.5 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700">
        Зарегистрироваться
      </button>
    </form>

    <p class="text-sm text-slate-500 mt-6 text-center">
      Уже есть аккаунт? <a href="login.php" class="text-brand-600 font-medium hover:underline">Войти</a>
    </p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
