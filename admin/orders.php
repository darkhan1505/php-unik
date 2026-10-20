<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$db = getDB();
$statuses = ['new', 'shipped', 'done', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;
    $status = $_POST['status'] ?? '';

    if ($orderId !== null && in_array($status, $statuses, true)) {
        $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        set_flash('success', 'Статус заказа обновлён.');
    } else {
        set_flash('error', 'Не удалось изменить статус.');
    }
    redirect('/admin/orders.php');
}

$orders = $db->query(
    'SELECT o.id, o.full_name, o.phone, o.address, o.total, o.status, o.created_at, u.email
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.id DESC'
)->fetchAll();

$itemsByOrder = [];
if ($orders) {
    foreach ($db->query('SELECT order_id, product_name, size, quantity FROM order_items ORDER BY id')->fetchAll() as $row) {
        $itemsByOrder[$row['order_id']][] = $row;
    }
}

$pageTitle = 'Заказы';
require __DIR__ . '/../includes/header.php';
?>

<section class="container section-sm">
  <h2>Заказы</h2>
  <?php require __DIR__ . '/_nav.php'; ?>

  <div class="table-wrap">
    <table>
      <thead><tr><th>№</th><th>Клиент</th><th>Состав</th><th>Сумма</th><th>Статус</th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><b>#<?= (int) $o['id'] ?></b><br><span class="muted"><?= e(format_datetime($o['created_at'])) ?></span></td>
            <td>
              <b><?= e($o['full_name']) ?></b><br>
              <?= e($o['phone']) ?><br>
              <span class="muted"><?= e($o['address']) ?><br><?= e($o['email']) ?></span>
            </td>
            <td>
              <?php foreach ($itemsByOrder[$o['id']] ?? [] as $it): ?>
                <div><?= e($it['product_name']) ?> (<?= e($it['size']) ?>) × <?= (int) $it['quantity'] ?></div>
              <?php endforeach; ?>
            </td>
            <td><b><?= format_price((int) $o['total']) ?></b></td>
            <td>
              <form method="post" class="actions">
                <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                <select name="status" style="width:auto; padding:8px 10px;">
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e(order_status_label($s)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-light btn-sm">ОК</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?>
          <tr><td colspan="5" class="muted">Заказов пока нет.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
