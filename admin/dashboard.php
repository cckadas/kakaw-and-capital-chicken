<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kakaw & Capital Chicken - Admin</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(120deg, #fffbe7 0%, #f2f2f2 100%); }
    .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 18px; padding: 0; box-shadow: 0 6px 32px rgba(0,0,0,0.13); }
    .admin-header { background: linear-gradient(90deg, #F7B801 80%, #fffbe7 100%); color: #fff; padding: 32px 40px 20px 40px; border-radius: 18px 18px 0 0; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(247,184,1,0.08); }
    .admin-header h1 { margin: 0; font-size: 2.2rem; font-weight: 800; letter-spacing: 1.5px; text-shadow: 0 2px 8px #fffbe7; }
    .admin-nav { display: flex; gap: 28px; }
    .admin-nav a { color: #F7B801; background: #fff; text-decoration: none; font-weight: 600; font-size: 1.13rem; padding: 10px 26px; border-radius: 22px; transition: background 0.2s, color 0.2s, box-shadow 0.2s; box-shadow: 0 2px 8px rgba(247,184,1,0.07); border: 2px solid #fffbe7; position: relative; }
    .admin-nav a.active, .admin-nav a:hover { background: #F7B801; color: #fff; box-shadow: 0 4px 16px #ffe7a0; border: 2px solid #ffe7a0; }
    .admin-nav a.logout { background: #fffbe7; color: #F7B801; border: 2px solid #F7B801; font-weight: 700; }
    .admin-nav a.logout:hover { background: #F7B801; color: #fff; }
    .admin-content { padding: 48px 48px 56px 48px; min-height: 320px; }
    .admin-content h2 { font-size: 1.5rem; font-weight: 700; color: #F7B801; margin-bottom: 10px; }
    .admin-content p { color: #555; font-size: 1.1rem; }
    /* Popup Modal */
    .modal-success-bg { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.18); align-items: center; justify-content: center; }
    .modal-success { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(247,184,1,0.13); padding: 36px 32px 28px 32px; min-width: 320px; max-width: 90vw; text-align: center; position: relative; }
    .modal-success h3 { color: #F7B801; font-size: 1.3rem; margin-bottom: 10px; }
    .modal-success p { color: #333; font-size: 1.08rem; margin-bottom: 18px; }
    .modal-success button { background: #F7B801; color: #fff; border: none; border-radius: 22px; font-size: 1.1rem; font-weight: 600; padding: 10px 32px; cursor: pointer; transition: background 0.2s; }
    .modal-success button:hover { background: #e6a800; }
    @media (max-width: 700px) {
      .container { padding: 0; }
      .admin-header, .admin-content { padding: 18px 4vw; }
      .admin-header h1 { font-size: 1.2rem; }
      .admin-nav { gap: 10px; }
      .admin-content { padding: 24px 4vw 32px 4vw; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="admin-header">
      <h1>Admin Dashboard</h1>
      <nav class="admin-nav">
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="accounts.php">Accounts</a>
        <a href ="sanitation.php">Sanitation</a>
        <a href="view_feedback.php">View Feedback</a>
        <a href="logout.php" class="logout">Log Out</a>
      </nav>
    </div>
    <div class="admin-content">
      <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h2>
      <p>Use the navigation above to manage products, orders, and user accounts.</p>
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
    }
    // Example usage: showSuccessModal('Product added successfully!');
  </script>
</body>
</html>
