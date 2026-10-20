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
        redirect('/profile.php');
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
        redirect('/profile.php');
    }
}

$stmt = $db->prepare('SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$itemsByOrder = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT order_id, product_name, size, quantity FROM order_items WHERE order_id IN ($in) ORDER BY id");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        $itemsByOrder[$row['order_id']][] = $row;
    }
}

$formName = $_POST['name'] ?? $user['name'];
$formEmail = $_POST['email'] ?? $user['email'];

$pageTitle = 'Профиль';
require __DIR__ . '/includes/header.php';
?>

<section class="container section-sm">
  <h2>Мои заказы</h2>

  <?php if (!$orders): ?>
    <p class="muted" style="margin-top:20px;">Вы ещё ничего не заказывали. <a href="/index.php#catalog" style="color:#2465d8;">Перейти в каталог</a></p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>№</th><th>Дата</th><th>Состав</th><th>Сумма</th><th>Статус</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><b>#<?= (int) $o['id'] ?></b></td>
              <td><?= e(format_datetime($o['created_at'])) ?></td>
              <td>
                <?php foreach ($itemsByOrder[$o['id']] ?? [] as $it): ?>
                  <div><?= e($it['product_name']) ?> (<?= e($it['size']) ?>) × <?= (int) $it['quantity'] ?></div>
                <?php endforeach; ?>
              </td>
              <td><b><?= format_price((int) $o['total']) ?></b></td>
              <td><span class="badge <?= order_status_badge($o['status']) ?>"><?= e(order_status_label($o['status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<section class="container section-sm" style="padding-top:0;">
  <div class="grid" style="align-items:start;">
    <div class="box">
      <h1>Мои данные</h1>
      <?php if ($errors): ?>
        <ul class="errors"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <form method="post" novalidate>
        <input type="hidden" name="form" value="profile">
        <div class="field">
          <label for="name">Имя</label>
          <input type="text" id="name" name="name" value="<?= e($formName) ?>" required>
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= e($formEmail) ?>" required>
        </div>
        <button type="submit" class="btn">Сохранить</button>
      </form>
    </div>

    <div class="box">
      <h1>Смена пароля</h1>
      <?php if ($passwordErrors): ?>
        <ul class="errors"><?php foreach ($passwordErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <form method="post" data-password-match-form novalidate>
        <input type="hidden" name="form" value="password">
        <div class="field">
          <label for="current_password">Текущий пароль</label>
          <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="field">
          <label for="new_password">Новый пароль</label>
          <input type="password" id="new_password" name="new_password" required minlength="6" data-password>
        </div>
        <div class="field">
          <label for="new_password_confirm">Повторите новый пароль</label>
          <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="6" data-password-confirm>
          <p data-password-hint class="hint hidden">Пароли не совпадают</p>
        </div>
        <button type="submit" class="btn">Изменить пароль</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
