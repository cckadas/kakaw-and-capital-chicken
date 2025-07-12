<?php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, address, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checkout - Kakaw & Capital Chicken</title>
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
      display: flex;
      align-items: center;
      gap: 2px;
      margin-right: 10px;
    }
    .qty-btn {
      background: #fffbe7;
      color: var(--primary);
      border: none;
      border-radius: 3px;
      width: 22px; height: 22px;
      font-size: 15px;
      cursor: pointer;
      font-weight: 500;
      padding: 0;
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
    .section-box.details {
      margin-top: 18px;
      padding-bottom: 18px;
    }
    .details-btns {
      display: flex;
      gap: 16px;
      justify-content: center;
      margin-top: 10px;
    }
    .details-btn {
      flex: 1;
      background: #fff;
      border: 2px solid #e0e0e0;
      color: #888;
      border-radius: 22px;
      font-size: 15px;
      font-weight: 500;
      padding: 10px 0;
      cursor: pointer;
      transition: border 0.2s, color 0.2s, background 0.2s;
    }
    .details-btn.active, .details-btn:focus {
      border: 2px solid var(--primary);
      color: var(--primary);
      background: #fffbe7;
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
      .section-box, .section-box.details {
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
      <button class="back-btn" onclick="window.location.href='home.php'" aria-label="Back">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      CHECKOUT
    </div>
    <div class="section-box">
      <div class="section-title">ORDER SUMMARY</div>
      <div class="order-list" id="orderList"></div>
      <div class="subtotal-row">
        <span>SUBTOTAL</span>
        <span id="subtotal">₱ 0.00</span>
      </div>
    </div>
    <div class="section-box details">
      <div class="section-title">CUSTOMER DETAILS</div>
      <div class="details-btns">
        <button class="details-btn active" id="pickupBtn" type="button">PICK UP</button>
        <button class="details-btn" id="deliveryBtn" type="button">DELIVERY</button>
      </div>
      <form id="customerForm" autocomplete="off" style="margin:10px 0 0 0;">
        <div style="padding:0 16px;display:flex;flex-direction:column;gap:8px;">
          <input type="text" id="fullName" placeholder="Full Name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" readonly style="padding:10px;border-radius:6px;border:1.5px solid #e0e0e0;font-size:15px;background:#f7f7f7;">
          <input type="text" id="address" placeholder="Address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" readonly style="padding:10px;border-radius:6px;border:1.5px solid #e0e0e0;font-size:15px;background:#f7f7f7;">
          <input type="email" id="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly style="padding:10px;border-radius:6px;border:1.5px solid #e0e0e0;font-size:15px;background:#f7f7f7;">
          <input type="text" id="date" readonly style="padding:10px;border-radius:6px;border:1.5px solid #e0e0e0;font-size:15px;background:#f7f7f7;">
          <input type="text" id="time" readonly style="padding:10px;border-radius:6px;border:1.5px solid #e0e0e0;font-size:15px;background:#f7f7f7;">
        </div>
      </form>
    </div>
    <div class="checkout-footer">
      <button class="proceed-btn" id="proceedBtn">PROCEED TO PAYMENT</button>
    </div>
  </div>
  <script>
    // Load cart from localStorage
    let cart = JSON.parse(localStorage.getItem('cart') || '{}');
    function saveCart() {
      localStorage.setItem('cart', JSON.stringify(cart));
    }
    function renderOrderList() {
      const orderList = document.getElementById('orderList');
      let subtotal = 0;
      orderList.innerHTML = '';
      const items = Object.values(cart);
      if (items.length === 0) {
        orderList.innerHTML = '<div style="color:#888;text-align:center;padding:30px 0;">Your cart is empty.</div>';
        document.getElementById('subtotal').textContent = '₱ 0.00';
        document.getElementById('proceedBtn').disabled = true;
        return;
      }
      items.forEach(item => {
        subtotal += item.price * item.qty;
        const row = document.createElement('div');
        row.className = 'order-item';
        row.innerHTML = `
          <div class="order-qty">
            <button class="qty-btn" onclick="changeQty(${item.id},-1)">-</button>
            <span>${item.qty}</span>
            <button class="qty-btn" onclick="changeQty(${item.id},1)">+</button>
          </div>
          <div class="order-info">
            <div class="order-title">${item.name}</div>
            ${item.category ? `<div class='order-desc'>${item.category}</div>` : ''}
          </div>
          <div class="order-price">₱ ${(item.price * item.qty).toFixed(2)}</div>
        `;
        orderList.appendChild(row);
      });
      document.getElementById('subtotal').textContent = '₱ ' + subtotal.toFixed(2);
      document.getElementById('proceedBtn').disabled = false;
    }
    function changeQty(id, delta) {
      if (cart[id]) {
        cart[id].qty += delta;
        if (cart[id].qty <= 0) delete cart[id];
        saveCart();
        renderOrderList();
      }
    }
    let orderType = 'pickup';
    document.getElementById('pickupBtn').onclick = function() {
      orderType = 'pickup';
      this.classList.add('active');
      document.getElementById('deliveryBtn').classList.remove('active');
    };
    document.getElementById('deliveryBtn').onclick = function() {
      orderType = 'delivery';
      this.classList.add('active');
      document.getElementById('pickupBtn').classList.remove('active');
    };
    // Set current date and time
    function pad(n) { return n < 10 ? '0'+n : n; }
    const now = new Date();
    document.getElementById('date').value = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate());
    document.getElementById('time').value = pad(now.getHours()) + ':' + pad(now.getMinutes());

    document.getElementById('proceedBtn').onclick = function() {
      // Save details to sessionStorage for payment page
      const details = {
        fullName: document.getElementById('fullName').value,
        address: document.getElementById('address').value,
        email: document.getElementById('email').value,
        date: document.getElementById('date').value,
        time: document.getElementById('time').value,
        orderType: orderType
      };
      sessionStorage.setItem('customerDetails', JSON.stringify(details));
      window.location.href = 'payment.php';
    };
    renderOrderList();
    window.changeQty = changeQty;
  </script>
</body>
</html>
