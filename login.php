<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('/index.php');
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
            redirect('/index.php');
        }
    }
}

$pageTitle = 'Вход';
require __DIR__ . '/includes/header.php';
?>

<section class="narrow">
  <div class="box">
    <h1>Вход в аккаунт</h1>
    <p class="muted">Рады видеть вас снова.</p>

    <?php if ($errors): ?>
      <ul class="errors"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
      </div>
      <div class="field">
        <label for="password">Пароль</label>
        <div class="row">
          <input type="password" id="password" name="password" required>
          <button type="button" data-toggle-password="password" class="btn btn-light btn-sm">Показать</button>
        </div>
      </div>
      <button type="submit" class="btn btn-block">Войти</button>
    </form>

    <p class="muted" style="margin-top:20px; text-align:center;">
      Нет аккаунта? <a href="/register.php" style="color:#2465d8; font-weight:bold;">Зарегистрироваться</a>
    </p>
    <p class="muted" style="margin-top:12px; text-align:center; font-size:13px;">
      Демо-администратор: admin@north.local / admin123
    </p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
