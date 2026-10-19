<?php
// require_once подключает файл один раз (повторно не подключит). __DIR__ — папка текущего файла.
// init.php запускает сессию, подключает БД и общие функции — он нужен каждой странице.
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    // redirect() отправляет заголовок Location и вызывает exit — код ниже не выполнится.
    redirect('/index.php');
}

$errors = [];
$email = '';

// $_SERVER['REQUEST_METHOD'] — HTTP-метод запроса: POST = форма отправлена, GET = страницу просто открыли.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ?? '' — значение по умолчанию, если ключа в $_POST нет. trim() убирает пробелы по краям.
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Введите email и пароль.';
    } else {
        // prepare() + execute() — подготовленный запрос: значения для ? передаются отдельно массивом (защита от SQL-инъекций).
        // fetch() вернёт одну строку как ассоциативный массив (или false), fetchAll() — все строки.
        $stmt = getDB()->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // password_verify() сравнивает введённый пароль с хешем из БД.
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Неверный email или пароль.';
        } else {
            // $_SESSION — массив, который PHP хранит на сервере между запросами (привязан к пользователю через cookie).
            $_SESSION['user_id'] = (int) $user['id'];
            // Новый ID сессии после входа — защита от подмены сессии.
            session_regenerate_id(true);
            // Flash-сообщение: текст кладётся в $_SESSION и показывается один раз на следующей загруженной странице.
            set_flash('success', 'С возвращением, ' . $user['name'] . '!');
            redirect('/index.php');
        }
    }
}

// Переменная будет доступна в header.php: файлы подключены через require, поэтому у них общая область видимости.
$pageTitle = 'Вход';
// require вставляет файл прямо сюда (в отличие от require_once — каждый раз). Ниже, после закрывающего тега PHP, идёт обычный HTML.
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
