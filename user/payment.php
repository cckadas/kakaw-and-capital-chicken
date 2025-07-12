<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json');
  $user_id = $_SESSION['user_id'];
  $details = json_decode($_POST['details'], true);
  $cart = json_decode($_POST['cart'], true);
  $payment_option = $_POST['payment_option'];
  $order_type = $details['orderType'];
  $total = 0;
  foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
  }
  // Insert order
  try {
    // Check if columns exist, add order_type and payment_method if missing
    $conn->query("ALTER TABLE orders ADD COLUMN order_type VARCHAR(20) NULL");
  } catch (Exception $e) {}
  try {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(30) NULL");
  } catch (Exception $e) {}
  try {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total, status, order_type, payment_method) VALUES (?, NOW(), ?, 'pending', ?, ?)");
    $stmt->execute([$user_id, $total, $order_type, $payment_option]);
    $order_id = $conn->lastInsertId();
    // Insert order items
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
      $itemStmt->execute([$order_id, $item['id'], $item['qty'], $item['price']]);
    }
    echo json_encode(['success' => true, 'order_id' => $order_id]);
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment - Kakaw & Capital Chicken</title>
  <style>
    :root {
      --primary: #F7B801;
      --text: #333;
      --light-gray: #f2f2f2;
      --border: #e0e0e0;
      --bg: #fff;
    }
    body {
      background: var(--light-gray);
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
    }
    .checkout-container {
      max-width: 420px;
      margin: 0 auto;
      background: #fff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
      border-radius: 0 0 18px 18px;
    }
    .checkout-header {
      display: flex;
      align-items: center;
      padding: 18px 18px 0 18px;
      background: #fff;
      border-radius: 0 0 0 0;
      font-size: 20px;
      font-weight: 600;
      letter-spacing: 0.5px;
      color: var(--text);
      position: sticky;
      top: 0;
      z-index: 2;
    }
    .back-btn {
      background: none;
      border: none;
      margin-right: 10px;
      cursor: pointer;
      padding: 0;
      display: flex;
      align-items: center;
    }
    .section-box {
      background: #f7f7f7;
      border-radius: 8px;
      margin: 18px 18px 0 18px;
      padding: 0 0 12px 0;
      box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    }
    .section-title {
      font-size: 15px;
      font-weight: 600;
      color: #888;
      padding: 10px 16px 6px 16px;
      border-radius: 8px 8px 0 0;
      background: #ededed;
      letter-spacing: 0.2px;
    }
    .order-list {
      padding: 0 12px;
    }
    .order-item {
      display: flex;
      align-items: center;
      border-bottom: 1px solid #ececec;
      padding: 10px 0 6px 0;
      font-size: 15px;
    }
    .order-qty {
      min-width: 30px;
      text-align: center;
      font-weight: 500;
      color: #333;
    }
    .order-info { flex: 1; }
    .order-title { font-weight: 600; }
    .order-desc { font-size: 12px; color: #888; margin-top: 2px; }
    .order-price { min-width: 80px; text-align: right; font-size: 15px; font-weight: 500; }
    .subtotal-row {
      display: flex;
      justify-content: space-between;
      font-size: 15px;
      font-weight: 600;
      padding: 10px 12px 0 12px;
      color: var(--text);
    }
    .shipping-row {
      display: flex;
      justify-content: space-between;
      font-size: 15px;
      font-weight: 400;
      padding: 2px 12px 0 12px;
      color: #888;
    }
    .grand-row {
      display: flex;
      justify-content: space-between;
      font-size: 16px;
      font-weight: 700;
      padding: 8px 12px 0 12px;
      color: var(--text);
    }
    .section-box.payment {
      margin-top: 18px;
      padding-bottom: 18px;
    }
    .payment-title {
      font-size: 15px;
      font-weight: 600;
      color: #888;
      padding: 10px 16px 6px 16px;
      background: #ededed;
      border-radius: 8px 8px 0 0;
      letter-spacing: 0.2px;
    }
    .payment-options {
      padding: 0 16px;
      margin-top: 10px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .pay-radio {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #fff;
      border-radius: 8px;
      border: 2px solid #e0e0e0;
      padding: 12px 10px;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      transition: border 0.2s, background 0.2s;
    }
    .pay-radio.selected {
      border: 2px solid var(--primary);
      background: #fffbe7;
      color: var(--primary);
    }
    .pay-radio input[type=radio] {
      accent-color: var(--primary);
      margin-right: 8px;
    }
    .checkout-footer {
      margin-top: auto;
      padding: 18px 0 18px 0;
      background: #ededed;
      border-radius: 0 0 18px 18px;
      display: flex;
      justify-content: center;
    }
    .proceed-btn {
      width: 92%;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 22px;
      font-size: 18px;
      font-weight: 600;
      padding: 13px 0;
      cursor: pointer;
      letter-spacing: 0.5px;
      transition: background 0.2s;
    }
    .proceed-btn:disabled {
      background: #ffe7a0;
      color: #aaa;
      cursor: not-allowed;
    }
    @media (max-width: 600px) {
      .checkout-container {
        max-width: 100vw;
        border-radius: 0;
      }
      .section-box, .section-box.payment {
        margin: 14px 4vw 0 4vw;
      }
      .checkout-header {
        padding: 18px 4vw 0 4vw;
      }
    }
  </style>
</head>
<body>
  <div class="checkout-container">
    <div class="checkout-header">
      <button class="back-btn" onclick="window.location.href='checkout.php'" aria-label="Back">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      PAYMENT
    </div>
    <div class="section-box">
      <div class="section-title">ORDER SUMMARY</div>
      <div class="order-list" id="orderList"></div>
      <div class="subtotal-row">
        <span>SUBTOTAL</span>
        <span id="subtotal">₱ 0.00</span>
      </div>
      <div class="shipping-row">
        <span>SHIPPING</span>
        <span id="shipping">₱ 0.00</span>
      </div>
      <div class="grand-row">
        <span>GRAND TOTAL</span>
        <span id="grandTotal">₱ 0.00</span>
      </div>
    </div>
    <div class="section-box payment">
      <div class="payment-title">PAYMENT OPTIONS</div>
      <div class="payment-options">
        <label class="pay-radio selected"><input type="radio" name="payOpt" value="card" checked> Credit/Debit Cards & E-Wallets</label>
        <label class="pay-radio"><input type="radio" name="payOpt" value="cod"> Cash On Pick Up</label>
      </div>
    </div>
    <div class="checkout-footer">
      <button class="proceed-btn" id="payNowBtn">PAY NOW</button>
    </div>
  </div>
  <script>
    // Load cart and customer details
    let cart = JSON.parse(localStorage.getItem('cart') || '{}');
    let details = {};
    try { details = JSON.parse(sessionStorage.getItem('customerDetails') || '{}'); } catch(e) {}
    function renderOrderList() {
      const orderList = document.getElementById('orderList');
      let subtotal = 0;
      orderList.innerHTML = '';
      const items = Object.values(cart);
      if (items.length === 0) {
        orderList.innerHTML = '<div style="color:#888;text-align:center;padding:30px 0;">Your cart is empty.</div>';
        document.getElementById('subtotal').textContent = '₱ 0.00';
        document.getElementById('grandTotal').textContent = '₱ 0.00';
        document.getElementById('payNowBtn').disabled = true;
        return;
      }
      items.forEach(item => {
        subtotal += item.price * item.qty;
        const row = document.createElement('div');
        row.className = 'order-item';
        row.innerHTML = `
          <div class="order-qty">${item.qty}</div>
          <div class="order-info">
            <div class="order-title">${item.name}</div>
            ${item.category ? `<div class='order-desc'>${item.category}</div>` : ''}
          </div>
          <div class="order-price">₱ ${(item.price * item.qty).toFixed(2)}</div>
        `;
        orderList.appendChild(row);
      });
      document.getElementById('subtotal').textContent = '₱ ' + subtotal.toFixed(2);
      document.getElementById('shipping').textContent = '₱ 0.00';
      document.getElementById('grandTotal').textContent = '₱ ' + subtotal.toFixed(2);
      document.getElementById('payNowBtn').disabled = false;
    }
    // Payment option selection
    document.querySelectorAll('.pay-radio').forEach(label => {
      label.onclick = function() {
        document.querySelectorAll('.pay-radio').forEach(l => l.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked = true;
      };
    });
    document.getElementById('payNowBtn').onclick = function() {
      const payOpt = document.querySelector('input[name=payOpt]:checked').value;
      // Save payment option
      sessionStorage.setItem('paymentOption', payOpt);
      // Submit order to backend
      fetch('payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'details=' + encodeURIComponent(sessionStorage.getItem('customerDetails')) +
              '&cart=' + encodeURIComponent(localStorage.getItem('cart')) +
              '&payment_option=' + encodeURIComponent(payOpt)
      })
      .then(res => res.json())
      .then(handlePaymentResponse);
    };
    // After successful payment, redirect to receipt page
    function handlePaymentResponse(res) {
      if (res.success && res.order_id) {
        localStorage.removeItem('cart');
        sessionStorage.removeItem('customerDetails');
        window.location.href = 'receipt.php?order_id=' + res.order_id;
      } else {
        alert(res.error || 'Payment failed.');
      }
    }
    renderOrderList();
  </script>
</body>
</html>
