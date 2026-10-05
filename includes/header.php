<!doctype html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — NORTH.' : 'NORTH. — магазин курток' ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header>
  <nav class="container">
    <a href="/index.php" class="logo">NORTH.</a>
    <button type="button" id="menu-toggle" class="menu-btn" aria-label="Открыть меню">&#9776;</button>
    <div class="links" id="nav-links">
      <a href="/index.php#catalog">Каталог</a>
      <a href="/cart.php">Корзина<span class="cart-count"><?= cart_count() ?></span></a>
      <?php if (is_logged_in()): ?>
        <a href="/profile.php"><?= e(current_user()['name']) ?></a>
        <?php if (is_admin()): ?>
          <a href="/admin/index.php" style="color:#2465d8; font-weight:bold;">Админ-панель</a>
        <?php endif; ?>
        <a href="/logout.php">Выйти</a>
      <?php else: ?>
        <a href="/login.php">Войти</a>
        <a href="/register.php" class="btn btn-sm">Регистрация</a>
      <?php endif; ?>
    </div>
  </nav>
</header>

<main>
  <?php foreach (get_flashes() as $type => $msg): ?>
    <div class="container">
      <div data-flash class="flash <?= $type === 'error' ? 'error' : '' ?>"><?= e($msg) ?></div>
    </div>
  <?php endforeach; ?>
