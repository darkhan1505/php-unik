<?php

/** Безопасный вывод строки в HTML. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}


function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $stmt = getDB()->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $found = $stmt->fetch();
        $user = $found ?: null;
    }

    return $user;
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Пожалуйста, войдите в систему, чтобы продолжить.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    require_login();
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        set_flash('error', 'Доступ запрещён: требуются права администратора.');
        redirect('dashboard.php');
    }
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

/** Метки статусов задач для отображения. */
function status_label(string $status): string
{
    return match ($status) {
        'todo' => 'К выполнению',
        'in_progress' => 'В процессе',
        'done' => 'Готово',
        default => $status,
    };
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'todo' => 'bg-slate-100 text-slate-700',
        'in_progress' => 'bg-amber-100 text-amber-700',
        'done' => 'bg-green-100 text-green-700',
        default => 'bg-slate-100 text-slate-700',
    };
}

function priority_label(string $priority): string
{
    return match ($priority) {
        'low' => 'Низкий',
        'medium' => 'Средний',
        'high' => 'Высокий',
        default => $priority,
    };
}

function priority_badge_class(string $priority): string
{
    return match ($priority) {
        'low' => 'bg-slate-100 text-slate-600',
        'medium' => 'bg-blue-100 text-blue-700',
        'high' => 'bg-red-100 text-red-700',
        default => 'bg-slate-100 text-slate-600',
    };
}

function format_date(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date('d.m.Y', $ts) : '';
}

/**
 * Тематические SVG-иконки (Heroicons, outline, MIT license) вместо эмодзи.
 */
function icon(string $name, string $class = 'w-6 h-6'): string
{
    $paths = [
        'badge-check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />',
        'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />',
        'lock-closed' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />',
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25 6-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
    ];

    $path = $paths[$name] ?? '';

    return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="' . $class . '">' . $path . '</svg>';
}
