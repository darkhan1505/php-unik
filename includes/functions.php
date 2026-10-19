<?php

/** Безопасный вывод строки в HTML. */
// function имя(тип $аргумент): тип_результата  объявление функции. ?string = строка или null.
// htmlspecialchars превращает < > " ' в безопасные HTML-сущности (защита от XSS); ??  значение по умолчанию для null.
// В HTML-шаблонах значение выводят коротким тегом (знак «равно» сразу после открывающего тега), он равен echo.
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// header('Location: ...') просит браузер перейти на другой URL. exit останавливает скрипт  иначе он выполнился бы дальше.
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// Flash-сообщения: пишем в сессию, показываем один раз (см. get_flashes и header.php).
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

// Забираем сообщения и сразу удаляем (unset) из сессии, чтобы не показывались повторно.
function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

// isset()  существует ли ключ в массиве и не равен ли он null.
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

// static  переменная сохраняется между вызовами функции: запрос в БД выполнится один раз за страницу.
// ?array  вернёт массив с данными пользователя или null. ?: ... ниже: «$found ?: null»  взять $found, а если он пустой/false  null.
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

// «Охранник» страницы: не вошёл  flash-ошибка и редирект на логин.
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

// !== строгое сравнение (проверяет и значение, и тип).
function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

/** Цена в тенге: 29990 -> "29 990 ₸". */
// number_format(число, знаков_после_запятой, разделитель_дробной_части, разделитель_тысяч).
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
// explode(',', 'S,M') → ['S','M']; array_map('trim') убирает пробелы; array_filter выкидывает пустые; array_values переиндексирует.
function parse_sizes(string $sizes): array
{
    return array_values(array_filter(array_map('trim', explode(',', $sizes)), 'strlen'));
}

// match  как switch, но возвращает значение; default  если ничего не подошло.
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

// Корзина в сессии: ['id:размер' => количество]. array_sum  сумма всех количеств; ?? []  пустой массив, если корзины нет.
function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

/**
 * Возвращает позиции корзины с данными товаров и итоговую сумму.
 * Товары, которых больше нет в каталоге или нет в наличии, удаляются из корзины.
 */
// fn ($x) => ...  короткая стрелочная функция. explode(':', ...)[0] берёт id из ключа вида '5:M'.
// [$id, $size] = ...  «деструктуризация»: раскладывает массив по переменным.
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
