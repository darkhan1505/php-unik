<!doctype html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . '  TaskFlow' : 'TaskFlow' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
            800: '#3730a3', 900: '#312e81'
          }
        }
      }
    }
  }
</script>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">

<nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      <a href="<?= is_logged_in() ? '/dashboard.php' : '/index.php' ?>" class="flex items-center gap-2 font-bold text-lg text-brand-700">
        TaskFlow
      </a>

      <div class="hidden md:flex items-center gap-1">
        <?php if (is_logged_in()): ?>
          <a href="/dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-brand-700">Дашборд</a>
          <a href="/tasks.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-brand-700">Задачи</a>
          <a href="/profile.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-brand-700">Профиль</a>
          <?php if (is_admin()): ?>
            <a href="/admin/index.php" class="px-3 py-2 rounded-md text-sm font-medium text-purple-600 hover:bg-purple-50">Админ-панель</a>
          <?php endif; ?>
          <span class="mx-2 h-6 w-px bg-slate-200"></span>
          <span class="px-2 text-sm text-slate-500">
            <?= e(current_user()['name']) ?>
          </span>
          <a href="/logout.php" class="px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50">Выйти</a>
        <?php else: ?>
          <a href="/login.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-brand-700">Войти</a>
          <a href="/register.php" class="ml-1 px-4 py-2 rounded-md text-sm font-semibold bg-brand-600 text-white hover:bg-brand-700">Регистрация</a>
        <?php endif; ?>
      </div>

      <button id="menu-toggle" class="md:hidden p-2 rounded-md text-slate-600 hover:bg-slate-100" aria-label="Открыть меню">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-1">
      <?php if (is_logged_in()): ?>
        <a href="/dashboard.php" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100">Дашборд</a>
        <a href="/tasks.php" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100">Задачи</a>
        <a href="/profile.php" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100">Профиль</a>
        <?php if (is_admin()): ?>
          <a href="/admin/index.php" class="block px-3 py-2 rounded-md text-sm font-medium text-purple-600 hover:bg-purple-50">Админ-панель</a>
        <?php endif; ?>
        <a href="/logout.php" class="block px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50">Выйти (<?= e(current_user()['name']) ?>)</a>
      <?php else: ?>
        <a href="/login.php" class="block px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-100">Войти</a>
        <a href="/register.php" class="block px-3 py-2 rounded-md text-sm font-semibold bg-brand-600 text-white text-center">Регистрация</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="flex-1 w-full">
  <?php foreach (get_flashes() as $type => $msg): ?>
    <div class="max-w-6xl mx-auto px-4 mt-4">
      <div data-flash class="rounded-lg px-4 py-3 text-sm border <?= $type === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' ?>">
        <?= e($msg) ?>
      </div>
    </div>
  <?php endforeach; ?>
