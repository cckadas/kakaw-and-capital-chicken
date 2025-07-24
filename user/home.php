<?php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// Fixed list of all categories
$categories = [
  'Starters', 'Solo Meals', 'Boodle Sets', 'Mains', 'Platters', 'Party Trays', 'Pancit & Pasta', 'Drinks', 'Dessert', 'Add Ons'
];

// Fetch all products
$prodStmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kakaw & Capital Chicken - Home</title>
  <style>
    :root {
      --primary: #F7B801;
      --text: #333;
      --light-gray: #f2f2f2;
      --border: #e0e0e0;
      --bg: #fff;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background-color: var(--light-gray);
      padding: 20px;
    }

    .header {
      background-color: var(--primary);
      color: white;
      padding: 16px;
      border-radius: 8px;
      text-align: center;
      margin-bottom: 20px;
    }

    .menu-icon {
      position: absolute;
      left: 20px;
      top: 18px;
      cursor: pointer;
      z-index: 1101;
      display: inline-block;
    }
    .header {
      position: relative;
      padding-left: 48px;
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: -260px;
      width: 240px;
      height: 100%;
      background: var(--primary);
      color: white;
      box-shadow: 2px 0 8px rgba(0,0,0,0.08);
      z-index: 1102;
      transition: left 0.3s;
      display: flex;
      flex-direction: column;
    }
    .sidebar.active {
      left: 0;
    }
    .sidebar-content {
      padding: 32px 20px 0 20px;
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    .sidebar-content a {
      color: white;
      text-decoration: none;
      font-size: 18px;
      font-weight: 500;
      transition: background 0.2s, color 0.2s;
      padding: 8px 0;
    }
    .sidebar-content a:first-child {
      font-size: 32px;
      align-self: flex-end;
      margin-bottom: 16px;
      cursor: pointer;
      color: white;
      text-decoration: none;
    }
    .sidebar-content a:hover {
      color: #fffbe7;
      background: rgba(0,0,0,0.07);
      border-radius: 4px;
    }
    .overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.2);
      z-index: 1100;
      display: none;
    }
    .overlay.active {
      display: block;
    }
    .main-content {
      display: flex;
      gap: 24px;
      margin: 40px auto 0 auto;
      max-width: 1100px;
      width: 100%;
      justify-content: center;
    }
    .categories {
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-width: 140px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      padding: 18px 8px;
      height: fit-content;
    }
    .category {
      background: none;
      border: none;
      color: var(--text);
      font-size: 15px;
      padding: 8px 10px;
      border-radius: 6px;
      text-align: left;
      cursor: pointer;
      transition: background 0.2s, color 0.2s;
    }
    .category.active, .category:hover {
      background: var(--primary);
      color: #fff;
    }
    .products {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 20px;
      flex: 1;
    }
    .product-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      padding: 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: box-shadow 0.2s;
    }
    .product-card img {
      width: 100%;
      max-width: 160px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 10px;
    }
    .product-info {
      text-align: center;
      margin-bottom: 10px;
    }
    .product-title {
      font-size: 16px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 4px;
    }
    .product-price {
      font-size: 15px;
      color: #888;
    }
    .add-btn {
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 18px;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.2s;
    }
    .add-btn:hover {
      background: #e0a700;
    }
    .cart-bar {
      position: fixed;
      left: 0; right: 0; bottom: 0;
      background: var(--primary);
      color: #fff;
      z-index: 1200;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
      min-height: 60px;
      max-width: 500px;
      margin: 0 auto;
      border-radius: 16px 16px 0 0;
      display: none;
    }
    .cart-bar-collapsed {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 14px;
      height: 54px;
      cursor: pointer;
      background: var(--primary);
      border-radius: 16px 16px 0 0;
      font-size: 16px;
      font-weight: 500;
      gap: 10px;
    }
    .cart-icon-bg {
      background: #e6a700;
      border-radius: 40px;
      padding: 4px 12px 4px 10px;
      display: flex;
      align-items: center;
      margin-right: 6px;
    }
    .cart-total-main {
      font-size: 16px;
      font-weight: 500;
      margin-right: 10px;
    }
    .cart-label {
      font-size: 16px;
      font-weight: 500;
      margin-right: 10px;
      letter-spacing: 0.5px;
    }
    .cart-arrow {
      font-size: 18px;
      color: #fff;
      margin-left: 4px;
      cursor: pointer;
      user-select: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .cart-details {
      display: none;
      background: var(--primary);
      border-radius: 0 0 16px 16px;
    }
    .cart-list {
      max-height: 160px;
      overflow-y: auto;
      padding: 8px 8px 0 8px;
    }
    .cart-item {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;
      background: rgba(255,255,255,0.06);
      border-radius: 6px;
      padding: 4px 6px;
    }
    .cart-img img {
      width: 28px; height: 28px; border-radius: 4px; object-fit: cover;
    }
    .cart-info { flex: 1; }
    .cart-title { font-size: 13px; font-weight: 400; }
    .cart-qty { display: flex; align-items: center; gap: 2px; margin-top: 2px; }
    .qty-btn {
      background: #fffbe7;
      color: var(--primary);
      border: none;
      border-radius: 3px;
      width: 18px; height: 18px;
      font-size: 13px;
      cursor: pointer;
      font-weight: 500;
      padding: 0;
    }
    .cart-price { min-width: 60px; text-align: right; font-size: 13px; font-weight: 400; }
    .remove-btn {
      background: none;
      border: none;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      margin-left: 4px;
      padding: 0 2px;
    }
    .cart-bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 10px;
      border-top: 1px solid #fffbe7;
      background: var(--primary);
      border-radius: 0 0 16px 16px;
    }
    .cart-total { font-size: 15px; font-weight: 500; }
    .checkout-btn {
      background: #fff;
      color: var(--primary);
      border: none;
      border-radius: 5px;
      padding: 7px 16px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      margin-left: 8px;
    }
    .checkout-btn:hover { background: #fffbe7; }
    .cart-footer-link {
      text-align: center;
      color: #888;
      font-size: 13px;
      margin: 0 auto 6px auto;
      max-width: 500px;
      display: none;
    }
    .cart-lock { font-size: 15px; margin-right: 4px; }
    @media (max-width: 900px) {
      .main-content {
        flex-direction: column;
        gap: 10px;
        max-width: 98vw;
      }
      .categories {
        flex-direction: row;
        overflow-x: auto;
        min-width: unset;
        padding: 10px 0 10px 10px;
        margin-bottom: 10px;
      }
      .category {
        min-width: 110px;
        text-align: center;
      }
    }
    @media (max-width: 600px) {
      .main-content {
        padding: 0 2vw;
      }
      .products {
        grid-template-columns: 1fr;
      }
      .cart-bar { max-width: 100vw; border-radius: 0; }
      .cart-footer-link { max-width: 100vw; }
    }
  </style>
</head>
<body>
  <div class="header">
    <span class="menu-icon" onclick="toggleMenu()">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </span>
    <h1>Kakaw & Capital Chicken Menu</h1>
  </div>
  <nav id="sidebarMenu" class="sidebar">
    <div class="sidebar-content">
      <a href="#" onclick="closeMenu()">&times;</a>
      <a href="account.php">Account</a>
      <a href="order_history.php">Order History</a>
      <a href="logout.php">Log Out</a>
      <a href="feedback.php">Send Feedback</a>
    </div>
  </nav>
  <div class="overlay" id="overlay" onclick="closeMenu()"></div>
  <div class="main-content">
    <aside class="categories" id="categories"></aside>
    <section class="products" id="products"></section>
  </div>
  <div class="cart-bar" id="cartBar">
    <div class="cart-bar-collapsed" id="cartBarCollapsed" onclick="toggleCartBar()">
      <span class="cart-icon-bg"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61l1.38-7.39H6"/></svg></span>
      <span class="cart-total-main" id="cartTotalMain">PHP 0.00</span>
      <span class="cart-label">CART</span>
      <span class="cart-arrow" id="cartArrow">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </span>
    </div>
    <div class="cart-details" id="cartDetails">
      <div class="cart-list" id="cartList"></div>
      <div class="cart-bottom">
        <span class="cart-total" id="cartTotal">PHP 0.00</span>
        <button class="checkout-btn" id="checkoutBtn">CHECKOUT &gt;</button>
        <span class="cart-arrow" onclick="toggleCartBar()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
        </span>
      </div>
    </div>
  </div>
  <div class="cart-footer-link"><span class="cart-lock">&#128274;</span> burpplediner-order.com</div>
  <script>
    const categories = <?php echo json_encode($categories); ?>;
    const products = <?php echo json_encode($products); ?>;
    let selectedCategory = categories[0] || '';
    let cart = JSON.parse(localStorage.getItem('cart') || '{}');
    let cartExpanded = false;

    function saveCart() {
      localStorage.setItem('cart', JSON.stringify(cart));
    }
    function addToCart(product) {
      if (!cart[product.id]) {
        cart[product.id] = { ...product, qty: 1 };
      } else {
        cart[product.id].qty++;
      }
      saveCart();
      renderCart();
    }
    function changeQty(id, delta) {
      if (cart[id]) {
        cart[id].qty += delta;
        if (cart[id].qty <= 0) delete cart[id];
        saveCart();
        renderCart();
      }
    }
    function removeFromCart(id) {
      delete cart[id];
      saveCart();
      renderCart();
    }
    function renderCategories() {
      const catEl = document.getElementById('categories');
      catEl.innerHTML = '';
      categories.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'category' + (cat === selectedCategory ? ' active' : '');
        btn.textContent = cat;
        btn.onclick = () => {
          selectedCategory = cat;
          renderCategories();
          renderProducts();
        };
        catEl.appendChild(btn);
      });
    }
    function renderProducts() {
      const prodEl = document.getElementById('products');
      prodEl.innerHTML = '';
      const filtered = products.filter(p => p.category === selectedCategory);
      if (filtered.length === 0) {
        prodEl.innerHTML = '<div style="text-align:center;color:#888;padding:40px 0;">No products in this category yet.</div>';
      } else {
        filtered.forEach(p => {
          const card = document.createElement('div');
          card.className = 'product-card';
          card.innerHTML = `
            <img src="${p.image ? '../' + p.image : 'https://via.placeholder.com/160x100?text=No+Image'}" alt="${p.name}">
            <div class="product-info">
              <div class="product-title">${p.name}</div>
              <div class="product-price">₱ ${parseFloat(p.price).toFixed(2)}</div>
            </div>
            <button class="add-btn">ADD</button>
          `;
          card.querySelector('.add-btn').onclick = () => addToCart({
            id: p.id,
            name: p.name,
            price: p.price,
            image: p.image,
            category: p.category
          });
          prodEl.appendChild(card);
        });
      }
    }
    function renderCart() {
      const cartList = document.getElementById('cartList');
      const cartBar = document.getElementById('cartBar');
      const cartTotal = document.getElementById('cartTotal');
      const cartTotalMain = document.getElementById('cartTotalMain');
      let total = 0;
      cartList.innerHTML = '';
      const items = Object.values(cart);
      if (items.length === 0) {
        cartBar.style.display = 'none';
        document.querySelector('.cart-footer-link').style.display = 'none';
        return;
      }
      cartBar.style.display = 'block';
      document.querySelector('.cart-footer-link').style.display = 'block';
      items.forEach(item => {
        total += item.price * item.qty;
        const row = document.createElement('div');
        row.className = 'cart-item';
        row.innerHTML = `
          <div class="cart-img"><img src="${item.image ? '../' + item.image : 'https://via.placeholder.com/40x40?text=No+Image'}" alt="${item.name}"></div>
          <div class="cart-info">
            <div class="cart-title">${item.name}</div>
            <div class="cart-qty">
              <button class="qty-btn" onclick="changeQty(${item.id},-1)">-</button>
              <span>${item.qty}</span>
              <button class="qty-btn" onclick="changeQty(${item.id},1)">+</button>
            </div>
          </div>
          <div class="cart-price">₱ ${(item.price * item.qty).toFixed(2)}</div>
          <button class="remove-btn" onclick="removeFromCart(${item.id})">&times;</button>
        `;
        cartList.appendChild(row);
      });
      cartTotal.textContent = 'PHP ' + total.toFixed(2);
      cartTotalMain.textContent = 'PHP ' + total.toFixed(2);
      // Show/hide details
      document.getElementById('cartDetails').style.display = cartExpanded ? 'block' : 'none';
      document.getElementById('cartBarCollapsed').style.display = cartExpanded ? 'none' : 'flex';
    }
    function toggleCartBar() {
      cartExpanded = !cartExpanded;
      renderCart();
    }
    window.changeQty = changeQty;
    window.removeFromCart = removeFromCart;
    renderCategories();
    renderProducts();
    renderCart();
    document.getElementById('checkoutBtn').onclick = function() {
      window.location.href = 'checkout.php';
    };
    function toggleMenu() {
      document.getElementById('sidebarMenu').classList.toggle('active');
      document.getElementById('overlay').classList.toggle('active');
    }
    function closeMenu() {
      document.getElementById('sidebarMenu').classList.remove('active');
      document.getElementById('overlay').classList.remove('active');
    }
  </script>
  <style>
    .cart-bar {
      position: fixed;
      left: 0; right: 0; bottom: 0;
      background: var(--primary);
      color: #fff;
      z-index: 1200;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
      min-height: 60px;
      max-width: 500px;
      margin: 0 auto;
      border-radius: 16px 16px 0 0;
      display: none;
    }
    .cart-bar-collapsed {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 14px;
      height: 54px;
      cursor: pointer;
      background: var(--primary);
      border-radius: 16px 16px 0 0;
      font-size: 16px;
      font-weight: 500;
      gap: 10px;
    }
    .cart-icon-bg {
      background: #e6a700;
      border-radius: 40px;
      padding: 4px 12px 4px 10px;
      display: flex;
      align-items: center;
      margin-right: 6px;
    }
    .cart-total-main {
      font-size: 16px;
      font-weight: 500;
      margin-right: 10px;
    }
    .cart-label {
      font-size: 16px;
      font-weight: 500;
      margin-right: 10px;
      letter-spacing: 0.5px;
    }
    .cart-arrow {
      font-size: 18px;
      color: #fff;
      margin-left: 4px;
      cursor: pointer;
      user-select: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .cart-details {
      display: none;
      background: var(--primary);
      border-radius: 0 0 16px 16px;
    }
    .cart-list {
      max-height: 160px;
      overflow-y: auto;
      padding: 8px 8px 0 8px;
    }
    .cart-item {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;
      background: rgba(255,255,255,0.06);
      border-radius: 6px;
      padding: 4px 6px;
    }
    .cart-img img {
      width: 28px; height: 28px; border-radius: 4px; object-fit: cover;
    }
    .cart-info { flex: 1; }
    .cart-title { font-size: 13px; font-weight: 400; }
    .cart-qty { display: flex; align-items: center; gap: 2px; margin-top: 2px; }
    .qty-btn {
      background: #fffbe7;
      color: var(--primary);
      border: none;
      border-radius: 3px;
      width: 18px; height: 18px;
      font-size: 13px;
      cursor: pointer;
      font-weight: 500;
      padding: 0;
    }
    .cart-price { min-width: 60px; text-align: right; font-size: 13px; font-weight: 400; }
    .remove-btn {
      background: none;
      border: none;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      margin-left: 4px;
      padding: 0 2px;
    }
    .cart-bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 10px;
      border-top: 1px solid #fffbe7;
      background: var(--primary);
      border-radius: 0 0 16px 16px;
    }
    .cart-total { font-size: 15px; font-weight: 500; }
    .checkout-btn {
      background: #fff;
      color: var(--primary);
      border: none;
      border-radius: 5px;
      padding: 7px 16px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      margin-left: 8px;
    }
    .checkout-btn:hover { background: #fffbe7; }
    .cart-footer-link {
      text-align: center;
      color: #888;
      font-size: 13px;
      margin: 0 auto 6px auto;
      max-width: 500px;
      display: none;
    }
    .cart-lock { font-size: 15px; margin-right: 4px; }
    @media (max-width: 900px) {
      .main-content {
        flex-direction: column;
        gap: 10px;
        max-width: 98vw;
      }
      .categories {
        flex-direction: row;
        overflow-x: auto;
        min-width: unset;
        padding: 10px 0 10px 10px;
        margin-bottom: 10px;
      }
      .category {
        min-width: 110px;
        text-align: center;
      }
    }
    @media (max-width: 600px) {
      .main-content {
        padding: 0 2vw;
      }
      .products {
        grid-template-columns: 1fr;
      }
      .cart-bar { max-width: 100vw; border-radius: 0; }
      .cart-footer-link { max-width: 100vw; }
    }
  </style>
</body>
</html>
