<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Handle delete
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
  $stmt->execute([$id]);
  header('Location: products.php');
  exit;
}

// Handle add/edit
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];
  $image = '';
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imgName = uniqid('prod_') . '_' . basename($_FILES['image']['name']);
    $targetDir = '../uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $targetFile = $targetDir . $imgName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
      $image = 'uploads/' . $imgName;
    }
  }
  if (isset($_POST['edit_id']) && $_POST['edit_id']) {
    // Edit
    $id = intval($_POST['edit_id']);
    if ($image) {
      $stmt = $conn->prepare('UPDATE products SET name=?, description=?, category=?, price=?, quantity=?, image=? WHERE id=?');
      $stmt->execute([$name, $description, $category, $price, $quantity, $image, $id]);
    } else {
      $stmt = $conn->prepare('UPDATE products SET name=?, description=?, category=?, price=?, quantity=? WHERE id=?');
      $stmt->execute([$name, $description, $category, $price, $quantity, $id]);
    }
    $message = 'Product updated!';
  } else {
    // Add
    $stmt = $conn->prepare('INSERT INTO products (name, description, category, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $description, $category, $price, $quantity, $image]);
    $message = 'Product added!';
  }
}

// Fetch products
$products = $conn->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
// Fetch for edit
$edit = null;
if (isset($_GET['edit'])) {
  $id = intval($_GET['edit']);
  $stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
  $stmt->execute([$id]);
  $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Products</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(120deg, #fffbe7 0%, #f2f2f2 100%); margin: 0; }
    .main-flex-wrap {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: flex-start;
      gap: 40px;
      max-width: 1200px;
      margin: 60px auto 60px auto;
      padding: 0 24px;
    }
    .centered-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(247,184,1,0.13);
      padding: 40px 36px 32px 36px;
      min-width: 340px;
      flex: 1 1 520px;
      margin-bottom: 0;
      max-width: 540px;
      align-self: flex-start;
      box-sizing: border-box;
    }
    .product-list-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 16px rgba(247,184,1,0.10);
      padding: 40px 36px 32px 36px;
      min-width: 600px;
      flex: 2 1 900px;
      max-width: 900px;
      align-self: flex-start;
      box-sizing: border-box;
    }
    h2 { margin-bottom: 18px; color: #F7B801; font-size: 1.5rem; font-weight: 700; }
    form { margin-bottom: 0; }
    input, textarea, select { width: 95%; padding: 14px; margin-bottom: 16px; border: 2px solid #ffe7a0; border-radius: 8px; font-size: 1rem; background: #fff; transition: border 0.2s; }
    input:focus, textarea:focus, select:focus { border: 2px solid #F7B801; outline: none; }
    .form-btn-row { display: flex; gap: 16px; align-items: center; }
    button { background: linear-gradient(90deg, #F7B801 80%, #ffe7a0 100%); color: #fff; border: none; border-radius: 22px; padding: 14px 38px; font-size: 1.15rem; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px #ffe7a0; transition: background 0.2s, color 0.2s; letter-spacing: 0.5px; margin-bottom: 0; }
    button:hover { background: #F7B801; color: #fffbe7; }
    .msg { color: #0a0; margin-bottom: 12px; font-weight: 600; background: #eaffea; border-radius: 8px; padding: 8px 16px; display: inline-block; }
    .error { color: #c00; margin-bottom: 12px; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 24px; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px #ffe7a0; }
    th, td { border: 1px solid #e0e0e0; padding: 12px 10px; text-align: left; font-size: 1rem; }
    th { background: #fffbe7; color: #F7B801; font-weight: 700; position: sticky; top: 0; z-index: 1; }
    tr:hover td { background: #fffbe7; transition: background 0.2s; }
    img { max-width: 60px; max-height: 60px; border-radius: 8px; box-shadow: 0 1px 4px #ffe7a0; }
    .actions { display: flex; gap: 8px; }
    .actions a { color: #F7B801; text-decoration: none; font-weight: 600; border-radius: 6px; padding: 8px 18px; background: #fffbe7; border: 2px solid #ffe7a0; transition: background 0.2s, color 0.2s; font-size: 1rem; }
    .actions a.delete { color: #fff; background: #c00; border: 2px solid #c00; }
    .actions a:hover { background: #F7B801; color: #fff; }
    .actions a.delete:hover { background: #a00; color: #fff; }
    .back-link { display: inline-block; margin-top: 18px; color: #F7B801; text-decoration: underline; font-weight: 600; }
    .back-link:hover { color: #c00; }
    /* Popup Modal */
    .modal-success-bg { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.18); align-items: center; justify-content: center; }
    .modal-success { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(247,184,1,0.13); padding: 36px 32px 28px 32px; min-width: 320px; max-width: 90vw; text-align: center; position: relative; }
    .modal-success h3 { color: #F7B801; font-size: 1.3rem; margin-bottom: 10px; }
    .modal-success p { color: #333; font-size: 1.08rem; margin-bottom: 18px; }
    .modal-success button { background: #F7B801; color: #fff; border: none; border-radius: 22px; font-size: 1.1rem; font-weight: 600; padding: 10px 32px; cursor: pointer; transition: background 0.2s; }
    .modal-success button:hover { background: #e6a800; }
    @media (max-width: 1100px) {
      .main-flex-wrap { flex-direction: column; align-items: center; gap: 32px; }
      .centered-card, .product-list-card { max-width: 98vw; min-width: 280px; margin-bottom: 32px; }
    }
    @media (max-width: 700px) {
      .main-flex-wrap { flex-direction: column; align-items: center; gap: 18px; }
      .centered-card, .product-list-card { padding: 14px 2vw; margin-bottom: 18px; }
      th, td { font-size: 0.95rem; }
    }
  </style>
</head>
<body>
  <div class="main-flex-wrap">
    <div class="centered-card">
            <a href="dashboard.php" class="back-link">Back to Dashboard</a>
      <h2 style="text-align:center; margin-bottom: 24px;"><?php echo $edit ? 'Edit Product' : 'Add Product'; ?></h2>
      <form method="POST" enctype="multipart/form-data" id="productForm">
        <input type="hidden" name="edit_id" value="<?php echo $edit['id'] ?? ''; ?>">
        <input type="text" name="name" placeholder="Product Name" required value="<?php echo htmlspecialchars($edit['name'] ?? ''); ?>" />
        <textarea name="description" placeholder="Description" required><?php echo htmlspecialchars($edit['description'] ?? ''); ?></textarea>
        <select name="category" required>
          <option value="">Select Category</option>
          <?php $cats = ['Starters','Solo Meals','Boodle Sets','Mains','Platters','Party Trays','Pancit & Pasta','Drinks','Dessert','Add Ons'];
          foreach ($cats as $cat): ?>
          <option value="<?php echo $cat; ?>" <?php if (($edit['category'] ?? '') === $cat) echo 'selected'; ?>><?php echo $cat; ?></option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="price" placeholder="Price" min="0" step="0.01" required value="<?php echo htmlspecialchars($edit['price'] ?? ''); ?>" />
        <input type="number" name="quantity" placeholder="Quantity" min="0" required value="<?php echo htmlspecialchars($edit['quantity'] ?? ''); ?>" />
        <input type="file" name="image" accept="image/*" <?php if (!$edit) echo 'required'; ?> />
        <?php if ($edit && $edit['image']): ?><img src="../<?php echo $edit['image']; ?>" style="max-width:80px;max-height:80px;display:block;margin-bottom:10px;" /><?php endif; ?>
        <div class="form-btn-row">
          <button type="submit"><?php echo $edit ? 'Update Product' : 'Add Product'; ?></button>
          <?php if ($edit): ?>
            <a href="products.php" class="back-link">Cancel Edit</a>
          <?php endif; ?>
        </div>
      </form>
      <?php if ($message) echo '<div class="msg">' . htmlspecialchars($message) . '</div>'; ?>
    </div>
    <div class="product-list-card">
      <h2 style="margin-top:0; text-align:center;">Product List</h2>
      <div class="table-wrap">
        <table>
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Description</th>
            <th>Category</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
          <?php foreach ($products as $prod): ?>
          <tr>
            <td><?php if ($prod['image']) echo '<img src="../' . $prod['image'] . '" style="max-width:60px;max-height:60px;" />'; ?></td>
            <td><?php echo htmlspecialchars($prod['name']); ?></td>
            <td><?php echo htmlspecialchars($prod['description']); ?></td>
            <td><?php echo htmlspecialchars($prod['category']); ?></td>
            <td>₱ <?php echo number_format($prod['price'],2); ?></td>
            <td><?php echo $prod['quantity']; ?></td>
            <td><?php echo $prod['created_at']; ?></td>
            <td class="actions">
              <a href="products.php?edit=<?php echo $prod['id']; ?>">Edit</a>
              <a href="products.php?delete=<?php echo $prod['id']; ?>" class="delete" onclick="return confirm('Delete this product?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>

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
      // Optionally reload after closing
      if (window._modalShouldReload) location.href = 'products.php';
    }
    // Show popup if redirected after add/edit/delete
    <?php if ($message): ?>
      showSuccessModal('<?php echo addslashes($message); ?>');
      window._modalShouldReload = true;
    <?php endif; ?>
    // Intercept form submit for AJAX add/edit
    document.getElementById('productForm').onsubmit = function(e) {
      e.preventDefault();
      var form = this;
      var formData = new FormData(form);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'products.php');
      xhr.onload = function() {
        if (xhr.status === 200) {
          try {
            var parser = new DOMParser();
            var doc = parser.parseFromString(xhr.responseText, 'text/html');
            var msg = doc.querySelector('.msg');
            if (msg) {
              showSuccessModal(msg.textContent);
              window._modalShouldReload = true;
            } else {
              showSuccessModal('Product saved!');
              window._modalShouldReload = true;
            }
          } catch(e) { showSuccessModal('Product saved!'); window._modalShouldReload = true; }
        }
      };
      xhr.send(formData);
    };
    // Intercept delete links for popup
    document.querySelectorAll('.actions a.delete').forEach(function(link) {
      link.addEventListener('click', function(e) {
        if (!confirm('Delete this product?')) return;
        e.preventDefault();
        fetch(this.href)
          .then(function() {
            showSuccessModal('Product deleted!');
            window._modalShouldReload = true;
          });
      });
    });
  </script>
</body>
</html>
