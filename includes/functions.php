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
        set_flash('error', 'Пожалуйста, войдите в аккаунт, чтобы продолжить.');
        redirect('/login.php');
    }
}

function require_admin(): void
{
    require_login();
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        set_flash('error', 'Доступ запрещён: требуются права администратора.');
        redirect('/index.php');
    }
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

/** Цена в тенге: 29990 -> "29 990 ₸". */
function format_price(int $price): string
{
    return number_format($price, 0, '', ' ') . ' ₸';
}

function format_datetime(?string $date): string
{
    $ts = $date ? strtotime($date) : false;
    return $ts ? date('d.m.Y H:i', $ts) : '';
}

/** Размеры товара из строки "S,M,L" в массив. */
function parse_sizes(string $sizes): array
{
    return array_values(array_filter(array_map('trim', explode(',', $sizes)), 'strlen'));
}

function order_status_label(string $status): string
{
    return match ($status) {
        'new' => 'Новый',
        'shipped' => 'Отправлен',
        'done' => 'Выполнен',
        'cancelled' => 'Отменён',
        default => $status,
    };
}

function order_status_badge(string $status): string
{
    return match ($status) {
        'new' => 'blue',
        'shipped' => 'amber',
        'done' => 'green',
        'cancelled' => 'red',
        default => '',
    };
}

/* ---------- Корзина (хранится в сессии: ключ "id:размер" => количество) ---------- */

function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

/**
 * Возвращает позиции корзины с данными товаров и итоговую сумму.
 * Товары, которых больше нет в каталоге или нет в наличии, удаляются из корзины.
 */
function cart_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return ['items' => [], 'total' => 0];
    }

    $ids = array_values(array_unique(array_map(fn ($key) => (int) explode(':', (string) $key)[0], array_keys($cart))));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = getDB()->prepare("SELECT id, name, price, image_url, in_stock FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = [];
    foreach ($stmt->fetchAll() as $p) {
        $products[(int) $p['id']] = $p;
    }

    $items = [];
    $total = 0;
    foreach ($cart as $key => $qty) {
        [$id, $size] = explode(':', (string) $key, 2);
        $p = $products[(int) $id] ?? null;
        if (!$p || !$p['in_stock']) {
            unset($_SESSION['cart'][$key]);
            continue;
        }
        $sum = (int) $p['price'] * (int) $qty;
        $total += $sum;
        $items[] = [
            'key' => $key,
            'product_id' => (int) $p['id'],
            'name' => $p['name'],
            'price' => (int) $p['price'],
            'image_url' => $p['image_url'],
            'size' => $size,
            'quantity' => (int) $qty,
            'sum' => $sum,
        ];
    }

    return ['items' => $items, 'total' => $total];
}
