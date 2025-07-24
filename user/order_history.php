<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Handle reorder
if (isset($_GET['reorder'])) {
    $order_id = intval($_GET['reorder']);

    // Clear current cart in session completely
    $_SESSION['cart'] = []; // Ensure cart is empty

    // Get items from the original order
    try {
        $stmt = $conn->prepare('
            SELECT oi.product_id, oi.quantity AS ordered_quantity, p.name, p.price, p.image, p.category
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ');
        $stmt->execute([$order_id]);
        $original_order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($original_order_items) > 0) {
            foreach ($original_order_items as $item) {
                $product_id = $item['product_id'];
                $ordered_quantity = $item['ordered_quantity'];

                // Add the item to the cart
                $_SESSION['cart'][$product_id] = [
                    'id'       => $product_id,
                    'name'     => htmlspecialchars($item['name']), // Sanitize name
                    'price'    => $item['price'],
                    'image'    => $item['image'],
                    'category' => $item['category'],
                    'qty'      => $ordered_quantity
                ];
            }
        } else {
            $_SESSION['checkout_message'] = 'Could not reorder items for Order #' . htmlspecialchars($order_id);
        }

    } catch (PDOException $e) {
        error_log('Database Error during reorder in order_history.php: ' . $e->getMessage());
        $_SESSION['cart'] = []; // Ensure cart is empty on error
    }

    header('Location: checkout.php');
    exit;
}

// Fetch orders for this user
try {
    $stmt = $conn->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC');
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch order items for each order
    $orderItems = [];
    foreach ($orders as $order) {
        $itemStmt = $conn->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
        $itemStmt->execute([$order['id']]);
        $orderItems[$order['id']] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Database Error fetching orders in order_history.php: ' . $e->getMessage());
    $orders = []; // Ensure orders array is empty on error
    $orderItems = []; // Ensure orderItems array is empty on error
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order History - Kakaw & Capital Chicken</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f2f2f2; margin: 0; }
    .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.10); padding: 0 0 32px 0; }
    .header { background: #F7B801; color: #fff; padding: 28px 32px 18px 32px; border-radius: 16px 16px 0 0; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; }
    h2 { margin: 0 0 18px 0; color: #F7B801; font-size: 1.2rem; font-weight: 700; padding: 0 32px; }
    .order-card { background: #fffbe7; border-radius: 12px; margin: 18px 32px; padding: 18px 18px 12px 18px; box-shadow: 0 2px 8px #ffe7a0; }
    .order-row { display: flex; justify-content: space-between; font-size: 1rem; margin-bottom: 6px; }
    .order-status { font-weight: 700; border-radius: 8px; padding: 2px 12px; font-size: 0.98rem; }
    .status-pending { color: #fff; background: #c00; }
    .status-preparing { color: #fff; background: #F7B801; }
    .status-out { color: #fff; background: #007bff; }
    .status-delivered, .status-completed { color: #fff; background: #0a0; }
    .status-cancelled { color: #fff; background: #888; }
    .order-items { margin: 8px 0 0 0; font-size: 0.98rem; color: #333; }
    .order-item { margin-bottom: 2px; }
    .order-date { color: #888; font-size: 0.95rem; }
    .reorder-btn {
      background: #F7B801;
      color: #fff;
      border: none;
      border-radius: 18px;
      padding: 8px 20px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin-top: 8px;
      transition: background 0.2s;
    }
    .reorder-btn:hover { background: #e6a800; }
    .back-link { display: inline-block; margin: 24px 32px 0 32px; color: #F7B801; text-decoration: underline; font-weight: 600; }
    .back-link:hover { color: #c00; }
    @media (max-width: 700px) {
      .container { max-width: 98vw; margin: 0 0 32px 0; }
      .order-card, .header, h2, .back-link { margin-left: 0; margin-right: 0; padding-left: 4vw; padding-right: 4vw; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">Order History</div>
    <h2>Your Orders</h2>
    <?php if (empty($orders)): ?>
      <div style="color:#888;text-align:center;padding:40px 0;">No orders found.</div>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="order-row"><span>Order #<?php echo htmlspecialchars($order['id']); ?></span><span class="order-status status-<?php echo str_replace(' ','-',strtolower($order['status'])); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></div>
          <div class="order-row order-date">Placed: <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></div>
          <div class="order-row"><span>Type:</span><span><?php echo htmlspecialchars(ucfirst($order['order_type'])); ?></span></div>
          <div class="order-row"><span>Total:</span><span>₱ <?php echo number_format($order['total'],2); ?></span></div>
          <div class="order-items">
            <?php foreach ($orderItems[$order['id']] as $item): ?>
              <div class="order-item">• <?php echo htmlspecialchars($item['name']); ?> x <?php echo htmlspecialchars($item['quantity']); ?> (₱<?php echo number_format($item['price'],2); ?>)</div>
            <?php endforeach; ?>
          </div>
          <a href="order_history.php?reorder=<?php echo htmlspecialchars($order['id']); ?>" class="reorder-btn" onclick="return confirm('This will replace your current cart with items from Order #<?php echo htmlspecialchars($order['id']); ?>. Continue?');">Reorder</a>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <a href="home.php" class="back-link">&larr; Back to Home</a>
  </div>
</body>
</html>