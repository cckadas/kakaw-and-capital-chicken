<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
if (!isset($_GET['order_id'])) {
  echo 'Order not found.';
  exit;
}
$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];
// Fetch order
$stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { echo 'Order not found.'; exit; }
// Fetch order items
$itemStmt = $conn->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$itemStmt->execute([$order_id]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch user info
$userStmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
// Status message
$statusMsg = [
  'pending' => 'Your order is confirmed',
  'preparing' => 'Your order is being prepared',
  'out for delivery' => 'Your order is on the way',
  'delivered' => 'Order delivered',
  'completed' => 'Order completed',
  'cancelled' => 'Order cancelled',
];
$status = strtolower($order['status']);
$msg = $statusMsg[$status] ?? 'Order update';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order Receipt - Kakaw & Capital Chicken</title>
  <style>
    body { background: #f7f7f7; font-family: 'Segoe UI', sans-serif; }
    .receipt-container {
      max-width: 400px; margin: 0 auto; background: #fff; border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.07); padding: 0 0 24px 0; min-height: 100vh;
      display: flex; flex-direction: column; align-items: stretch;
    }
    .receipt-header {
      background: #F7B801; color: #fff; padding: 22px 0 14px 0; border-radius: 14px 14px 0 0;
      font-size: 1.15rem; font-weight: 700; letter-spacing: 0.5px; text-align: center;
    }
    .home-link {
      display: block; margin: 16px 0 0 0; color: #F7B801; text-decoration: underline; font-weight: 600;
      text-align: left; padding-left: 24px; font-size: 0.98rem;
    }
    .status-box {
      background: #f9f9f9; border-radius: 8px; margin: 18px 18px 0 18px; padding: 10px 14px 8px 14px;
      font-size: 1.01rem; color: #333; box-shadow: 0 1px 4px #ffe7a0;
      display: flex; flex-direction: column; gap: 2px;
    }
    .order-summary, .order-details {
      background: #fff; border-radius: 8px; margin: 16px 18px 0 18px; padding: 10px 15px 6px 10px;
      box-shadow: 0 1px 4px #ffe7a0; font-size: 0.98rem;
    }
    .order-summary-title, .order-details-title {
      font-weight: 700; color: #F7B801; font-size: 1.01rem; margin-bottom: 8px; padding: 0 18px;
    }
    .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
    .summary-table th, .summary-table td { font-size: 0.97rem; padding: 4px 0; text-align: left; }
    .summary-table th.qty, .summary-table td.qty { width: 28px; text-align: center; }
    .summary-table th.price, .summary-table td.price { width: 70px; text-align: right; }
    .summary-table th.item, .summary-table td.item { }
    .summary-table tfoot td { font-weight: 600; color: #222; }
    .summary-table th { color: #888; font-weight: 500; }
    .order-details-row { margin-bottom: 7px; padding: 0 18px; }
    .order-details-label { color: #888; font-size: 0.97rem; }
    .order-details-value { font-size: 1.01rem; font-weight: 500; color: #333; }
    .footer-link { text-align: center; color: #bbb; font-size: 13px; margin: 18px auto 0 auto; }
    @media (max-width: 600px) {
      .receipt-container { max-width: 100vw; border-radius: 0; }
      .receipt-header, .status-box, .order-summary, .order-details { margin-left: 0; margin-right: 0; padding-left: 0; padding-right: 0; }
      .order-details-row, .order-summary-title, .order-details-title { padding-left: 4vw; padding-right: 4vw; }
      .home-link { padding-left: 4vw; }
    }
  </style>
</head>
<body>
  <div class="receipt-container">
    <div class="receipt-header">Thank you, <?php echo htmlspecialchars($user['full_name']); ?>!</div>
    <a href="home.php" class="home-link">&larr; Home</a>
    <div class="status-box">
      <div style="font-weight:600;"><?php echo $msg; ?></div>
      <div style="font-size:0.97rem; margin-top:1px;">Order #<?php echo $order['id']; ?></div>
      <?php if ($status === 'out for delivery'): ?>
        <div style="margin-top:2px; font-size:0.97rem;">Tracking Number<br><span style="color:#007bff;">Tracking link</span></div>
      <?php endif; ?>
    </div>
    <div class="order-summary">
      <div class="order-summary-title">Order Summary</div>
      <table class="summary-table">
        <thead>
          <tr><th class="qty">Qty</th><th class="item">Item</th><th class="price">Price</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td class="qty"><?php echo $item['quantity']; ?></td>
              <td class="item"><?php echo htmlspecialchars($item['name']); ?></td>
              <td class="price">₱ <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td></td><td>Subtotal</td><td class="price">₱ <?php echo number_format($order['total'], 2); ?></td></tr>
          <tr><td></td><td>Shipping</td><td class="price">₱ <?php echo ($order['order_type'] === 'delivery' ? '100.00' : '0.00'); ?></td></tr>
          <tr><td></td><td>Grand Total</td><td class="price">₱ <?php echo number_format($order['total'] + ($order['order_type'] === 'delivery' ? 100 : 0), 2); ?></td></tr>
        </tfoot>
      </table>
    </div>
    <div class="order-details">
      <div class="order-details-title">Order Details</div>
      <div class="order-details-row"><span class="order-details-label">Time & Date</span><br><span class="order-details-value"><?php echo date('g:iA M d, Y', strtotime($order['order_date'])); ?></span></div>
      <div class="order-details-row"><span class="order-details-label">Contact</span><br><span class="order-details-value"><?php echo htmlspecialchars($user['email']); ?></span></div>
      <div class="order-details-row"><span class="order-details-label">Name</span><br><span class="order-details-value"><?php echo htmlspecialchars($user['full_name']); ?></span></div>
      <div class="order-details-row"><span class="order-details-label">Address</span><br><span class="order-details-value"><?php echo htmlspecialchars($user['address']); ?></span></div>
    </div>
    <div class="footer-link"></div>
  </div>
</body>
</html>
