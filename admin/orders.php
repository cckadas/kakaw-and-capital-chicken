<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Handle status update
if (isset($_POST['order_id'], $_POST['status'])) {
  $order_id = $_POST['order_id'];
  $new_status = $_POST['status'];
  $stmt = $conn->prepare('UPDATE orders SET status=? WHERE id=?');
  $stmt->execute([$new_status, $order_id]);

  // If status is delivered or completed, decrease product quantity
  if (in_array(strtolower($new_status), ['delivered', 'completed'])) {
    // Get order items
    $itemStmt = $conn->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
    $itemStmt->execute([$order_id]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
      $updateProd = $conn->prepare('UPDATE products SET quantity = GREATEST(quantity - ?, 0) WHERE id = ?');
      $updateProd->execute([$item['quantity'], $item['product_id']]);
    }
  }
  exit('ok');
}

// Fetch orders with user info
$orders = $conn->query('SELECT o.*, u.full_name, u.address, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC')->fetchAll(PDO::FETCH_ASSOC);
// Fetch order items for modal/details
$orderItems = [];
foreach ($orders as $order) {
  $stmt = $conn->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
  $stmt->execute([$order['id']]);
  $orderItems[$order['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$statusOptions = ['pending','preparing','out for delivery','delivered','completed','cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(120deg, #fffbe7 0%, #f2f2f2 100%); }
    .centered-card { max-width: 1100px; margin: 48px auto 32px auto; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(247,184,1,0.13); padding: 36px 32px 28px 32px; }
    h2 { margin-bottom: 18px; color: #F7B801; font-size: 1.5rem; font-weight: 700; text-align: center; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 24px; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px #ffe7a0; }
    th, td { border: 1px solid #e0e0e0; padding: 14px 12px; text-align: left; font-size: 1rem; }
    th { background: #fffbe7; color: #F7B801; font-weight: 700; position: sticky; top: 0; z-index: 1; }
    tr:hover td { background: #fffbe7; transition: background 0.2s; }
    .status-select { padding: 10px 18px; border-radius: 8px; border: 2px solid #ffe7a0; font-size: 1rem; background: #fff; transition: border 0.2s; min-width: 120px; font-weight: 600; }
    .status-select:focus { border: 2px solid #F7B801; outline: none; }
    .order-items { font-size: 13px; color: #555; margin-top: 4px; }
    .order-details { font-size: 13px; color: #888; }
    .back-link { display: inline-block; margin-top: 18px; color: #F7B801; text-decoration: underline; font-weight: 600; }
    .back-link:hover { color: #c00; }
    .status-pending { color: #fff; background: #c00; border-radius: 8px; padding: 4px 12px; font-weight: 700; }
    .status-preparing { color: #fff; background: #F7B801; border-radius: 8px; padding: 4px 12px; font-weight: 700; }
    .status-out { color: #fff; background: #007bff; border-radius: 8px; padding: 4px 12px; font-weight: 700; }
    .status-delivered, .status-completed { color: #fff; background: #0a0; border-radius: 8px; padding: 4px 12px; font-weight: 700; }
    .status-cancelled { color: #fff; background: #888; border-radius: 8px; padding: 4px 12px; font-weight: 700; }
    /* Popup Modal */
    .modal-success-bg { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.18); align-items: center; justify-content: center; }
    .modal-success { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(247,184,1,0.13); padding: 36px 32px 28px 32px; min-width: 320px; max-width: 90vw; text-align: center; position: relative; }
    .modal-success h3 { color: #F7B801; font-size: 1.3rem; margin-bottom: 10px; }
    .modal-success p { color: #333; font-size: 1.08rem; margin-bottom: 18px; }
    .modal-success button { background: #F7B801; color: #fff; border: none; border-radius: 22px; font-size: 1.1rem; font-weight: 600; padding: 10px 32px; cursor: pointer; transition: background 0.2s; }
    .modal-success button:hover { background: #e6a800; }
    @media (max-width: 900px) {
      .centered-card { max-width: 98vw; padding: 18px 2vw; }
      .modal-success { min-width: 90vw; }
    }
    @media (max-width: 600px) {
      .centered-card { padding: 10px 2vw; }
      .modal-success { min-width: 98vw; }
      th, td { font-size: 0.95rem; }
    }
  </style>
</head>
<body>
  <div class="centered-card">
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>

    <h2>Order Management</h2>
    <div class="table-wrap">
      <table>
        <tr>
          <th>Order #</th>
          <th>Date</th>
          <th>Customer</th>
          <th>Type</th>
          <th>Items</th>
          <th>Total</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
          <td>#<?php echo $order['id']; ?></td>
          <td><?php echo $order['order_date']; ?></td>
          <td>
            <?php echo htmlspecialchars($order['full_name']); ?><br>
            <span class="order-details">Email: <?php echo htmlspecialchars($order['email']); ?><br>Address: <?php echo htmlspecialchars($order['address']); ?></span>
          </td>
          <td><?php echo htmlspecialchars(ucfirst($order['order_type'])); ?></td>
          <td>
            <?php foreach ($orderItems[$order['id']] as $item): ?>
              <div class="order-items">
                <?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?> (₱<?php echo number_format($item['price'],2); ?>)
              </div>
            <?php endforeach; ?>
          </td>
          <td>₱ <?php echo number_format($order['total'],2); ?></td>
          <td>
            <form method="POST" onsubmit="return false;">
              <select class="status-select status-<?php echo str_replace(' ','-',strtolower($order['status'])); ?>" onchange="updateStatus(<?php echo $order['id']; ?>, this.value)">
                <?php foreach ($statusOptions as $opt): ?>
                  <option value="<?php echo $opt; ?>" <?php if ($order['status'] === $opt) echo 'selected'; ?>><?php echo ucfirst($opt); ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td><!-- For future: view, print, etc. --></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <!-- Success Modal Popup -->
  <div class="modal-success-bg" id="successModalBg">
    <div class="modal-success">
      <h3 id="successModalTitle">Success!</h3>
      <p id="successModalMsg">Action completed successfully.</p>
      <button onclick="closeSuccessModal()">OK</button>
    </div>
  </div>
  <script>
    // Show the success modal with a custom message
    function showSuccessModal(msg, title = 'Success!') {
      document.getElementById('successModalTitle').textContent = title;
      document.getElementById('successModalMsg').textContent = msg;
      document.getElementById('successModalBg').style.display = 'flex';
    }
    function closeSuccessModal() {
      document.getElementById('successModalBg').style.display = 'none';
      if (window._modalShouldReload) location.reload();
    }
    function updateStatus(orderId, status) {
      fetch('orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_id=' + orderId + '&status=' + encodeURIComponent(status)
      })
      .then(function(res) { return res.text(); })
      .then(function(txt) {
        if (txt.trim() === 'ok') {
          showSuccessModal('Order status updated!');
          window._modalShouldReload = true;
        }
      });
    }
  </script>
</body>
</html>
