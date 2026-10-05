<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('/index.php');
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
        redirect('/index.php');
    }
}

$pageTitle = 'Регистрация';
require __DIR__ . '/includes/header.php';
?>


<section class="narrow">
  <div class="box">
    <h1>Создать аккаунт</h1>
    <p class="muted">Это бесплатно и займёт меньше минуты.</p>

    <?php if ($errors): ?>
      <ul class="errors"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <form method="post" data-password-match-form novalidate>
      <div class="field">
        <label for="name">Имя</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" required minlength="2">
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
      </div>
      <div class="field">
        <label for="password">Пароль</label>
        <div class="row">
          <input type="password" id="password" name="password" required minlength="6" data-password>
          <button type="button" data-toggle-password="password" class="btn btn-light btn-sm">Показать</button>
        </div>
      </div>
      <div class="field">
        <label for="password_confirm">Повторите пароль</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="6" data-password-confirm>
        <p data-password-hint class="hint hidden">Пароли не совпадают</p>
      </div>
      <button type="submit" class="btn btn-block">Зарегистрироваться</button>
    </form>

    <p class="muted" style="margin-top:20px; text-align:center;">
      Уже есть аккаунт? <a href="/login.php" style="color:#2465d8; font-weight:bold;">Войти</a>
    </p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
