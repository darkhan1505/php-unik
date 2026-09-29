<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$pageTitle = 'Главная';
require __DIR__ . '/includes/header.php';
?>

<section class="max-w-6xl mx-auto px-4 py-16 sm:py-24">
  <div class="grid md:grid-cols-2 gap-12 items-center">
    <div>
      <span class="inline-block px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-semibold mb-4">Учебный проект · PHP + PostgreSQL + Tailwind</span>
      <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight mb-6">
        Управляйте задачами <span class="text-brand-600">просто и наглядно</span>
      </h1>
      <p class="text-lg text-slate-600 mb-8">
        TaskFlow  простой менеджер задач с регистрацией, авторизацией и ролями пользователей.
        Создавайте задачи, распределяйте их по категориям, отслеживайте статус выполнения.
      </p>
      <div class="flex flex-wrap gap-4">
        <a href="register.php" class="px-6 py-3 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700 shadow-sm">Начать бесплатно</a>
        <a href="login.php" class="px-6 py-3 rounded-lg bg-white text-slate-700 font-semibold border border-slate-200 hover:bg-slate-100">У меня уже есть аккаунт</a>
      </div>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-4"><?= icon('badge-check', 'w-5 h-5') ?></div>
        <h3 class="font-semibold text-slate-800 mb-1">Учёт задач</h3>
        <p class="text-sm text-slate-500">Статусы, приоритеты и сроки  всё под контролем.</p>
      </div>
      <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mb-4"><?= icon('tag', 'w-5 h-5') ?></div>
        <h3 class="font-semibold text-slate-800 mb-1">Категории</h3>
        <p class="text-sm text-slate-500">Группируйте задачи по темам и проектам.</p>
      </div>
      <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mb-4"><?= icon('lock-closed', 'w-5 h-5') ?></div>
        <h3 class="font-semibold text-slate-800 mb-1">Безопасность</h3>
        <p class="text-sm text-slate-500">Хэширование паролей, защита от SQL-инъекций.</p>
      </div>
      <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-4"><?= icon('users', 'w-5 h-5') ?></div>
        <h3 class="font-semibold text-slate-800 mb-1">Роли</h3>
        <p class="text-sm text-slate-500">Обычные пользователи и администратор.</p>
      </div>
    </div>
  </div>
</section>

<section class="bg-white border-y border-slate-200">
  <div class="max-w-6xl mx-auto px-4 py-16">
    <div class="grid md:grid-cols-2 gap-10 items-center">
      <img src="https://picsum.photos/seed/taskflow-desk/900/700" alt="Рабочий стол с ноутбуком и списком задач"
           class="rounded-2xl shadow-sm w-full h-72 sm:h-96 object-cover">
      <div>
        <span class="inline-block px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold mb-4">О проекте</span>
        <h2 class="text-3xl font-bold text-slate-900 mb-4">Порядок в задачах  залог продуктивной учёбы и работы</h2>
        <p class="text-slate-600 mb-4">
          TaskFlow помогает не держать всё в голове: заводите задачи, назначайте категории и приоритеты,
          отмечайте прогресс и видьте общую картину на дашборде.
        </p>
        <ul class="space-y-2 text-slate-600">
          <li class="flex items-center gap-2"><span class="text-green-600 flex-shrink-0"><?= icon('check-circle', 'w-5 h-5') ?></span> Личный кабинет с собственной статистикой</li>
          <li class="flex items-center gap-2"><span class="text-green-600 flex-shrink-0"><?= icon('check-circle', 'w-5 h-5') ?></span> Гибкая фильтрация по статусу и категории</li>
          <li class="flex items-center gap-2"><span class="text-green-600 flex-shrink-0"><?= icon('check-circle', 'w-5 h-5') ?></span> Административная панель для контроля всей системы</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 py-16">
  <div class="text-center max-w-2xl mx-auto mb-10">
    <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold mb-4">Возможности в действии</span>
    <h2 class="text-3xl font-bold text-slate-900">Как это выглядит на практике</h2>
  </div>
  <div class="grid sm:grid-cols-3 gap-6">
    <figure class="rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-sm">
      <img src="https://picsum.photos/seed/taskflow-planning/600/450" alt="Планирование задач за столом" class="w-full h-48 object-cover">
      <figcaption class="p-5">
        <h3 class="font-semibold text-slate-800 mb-1">Планирование</h3>
        <p class="text-sm text-slate-500">Создавайте задачи с описанием, сроком и приоритетом за пару секунд.</p>
      </figcaption>
    </figure>
    <figure class="rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-sm">
      <img src="https://picsum.photos/seed/taskflow-team/600/450" alt="Совместная работа команды" class="w-full h-48 object-cover">
      <figcaption class="p-5">
        <h3 class="font-semibold text-slate-800 mb-1">Категории и роли</h3>
        <p class="text-sm text-slate-500">Группируйте задачи по темам, а администратор видит всех пользователей.</p>
      </figcaption>
    </figure>
    <figure class="rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-sm">
      <img src="https://picsum.photos/seed/taskflow-progress/600/450" alt="Отслеживание прогресса выполнения задач" class="w-full h-48 object-cover">
      <figcaption class="p-5">
        <h3 class="font-semibold text-slate-800 mb-1">Контроль прогресса</h3>
        <p class="text-sm text-slate-500">Дашборд показывает, сколько задач в работе, а сколько уже готово.</p>
      </figcaption>
    </figure>
  </div>
</section>

<section class="bg-slate-100">
  <div class="max-w-6xl mx-auto px-4 py-16">
    <div class="text-center max-w-2xl mx-auto mb-10">
      <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold mb-4">Отзывы</span>
      <h2 class="text-3xl font-bold text-slate-900">Что говорят пользователи</h2>
    </div>
    <div class="grid sm:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
        <p class="text-slate-600 text-sm mb-4">«Наконец перестал забывать про дедлайны. Все задачи в одном месте, удобные фильтры.»</p>
        <div class="flex items-center gap-3">
          <img src="https://i.pravatar.cc/80?img=12" alt="Аватар пользователя Алии" class="w-10 h-10 rounded-full object-cover">
          <div>
            <p class="font-semibold text-slate-800 text-sm">Алия К.</p>
            <p class="text-xs text-slate-400">Студентка</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
        <p class="text-slate-600 text-sm mb-4">«Категории и приоритеты  то, чего не хватало в обычном блокноте. Пользуюсь каждый день.»</p>
        <div class="flex items-center gap-3">
          <img src="https://i.pravatar.cc/80?img=32" alt="Аватар пользователя Данияра" class="w-10 h-10 rounded-full object-cover">
          <div>
            <p class="font-semibold text-slate-800 text-sm">Данияр С.</p>
            <p class="text-xs text-slate-400">Разработчик</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
        <p class="text-slate-600 text-sm mb-4">«Как администратору мне удобно видеть всех пользователей и их задачи в одном месте.»</p>
        <div class="flex items-center gap-3">
          <img src="https://i.pravatar.cc/80?img=47" alt="Аватар пользователя Жанны" class="w-10 h-10 rounded-full object-cover">
          <div>
            <p class="font-semibold text-slate-800 text-sm">Жанна Т.</p>
            <p class="text-xs text-slate-400">Руководитель проекта</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="max-w-4xl mx-auto px-4 py-16 text-center">
  <h2 class="text-3xl font-bold text-slate-900 mb-4">Готовы навести порядок в задачах?</h2>
  <p class="text-slate-600 mb-8">Регистрация занимает меньше минуты  попробуйте прямо сейчас.</p>
  <a href="register.php" class="inline-block px-8 py-3 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700 shadow-sm">Создать аккаунт</a>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
