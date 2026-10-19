<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__  папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции  он нужен каждой странице.
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    // redirect() отправляет заголовок Location и вызывает exit  код ниже не выполнится.
    redirect('/index.php');
}

$errors = [];
$name = '';
$email = '';

// $_SERVER['REQUEST_METHOD']  HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ?? ''  значение по умолчанию, если ключа в $_POST нет. trim() убирает пробелы по краям.
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    // mb_strlen()  длина строки с учётом UTF-8 (кириллица); обычный strlen считает байты.
    if ($name === '' || mb_strlen($name) < 2) {
        $errors[] = 'Имя должно содержать минимум 2 символа.';
    }
    // filter_var() с FILTER_VALIDATE_EMAIL проверяет формат email.
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
        // prepare() + execute()  подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
        // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll()  все строки.
        $stmt = getDB()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким email уже зарегистрирован.';
        }
    }

    if (!$errors) {
        // password_hash() делает безопасный хеш пароля (bcrypt)  пароль в открытом виде в БД не хранится.
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = getDB()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $hash, 'user']);

        // $_SESSION  массив, который PHP хранит на сервере между запросами (привязан к пользователю через cookie).
        $_SESSION['user_id'] = (int) getDB()->lastInsertId();
        // Новый ID сессии после входа  защита от подмены сессии.
        session_regenerate_id(true);

        // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
        set_flash('success', 'Регистрация прошла успешно! Добро пожаловать, ' . $name . '.');
        redirect('/index.php');
    }
}

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Регистрация';
// require вставляет файл прямо сюда (в отличие от require_once  каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
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
