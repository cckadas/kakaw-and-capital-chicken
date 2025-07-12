<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Handle delete
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  // Check for related orders
  $orderCheck = $conn->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
  $orderCheck->execute([$id]);
  $hasOrders = $orderCheck->fetchColumn() > 0;
  if ($hasOrders) {
    $message = 'Cannot delete user: This account has related orders.';
  } else {
    $stmt = $conn->prepare('DELETE FROM users WHERE id = ? AND role != "admin"');
    $stmt->execute([$id]);
    header('Location: accounts.php');
    exit;
  }
}

// Handle edit
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $id = intval($_POST['edit_id']);
  $full_name = $_POST['full_name'];
  $mobile = $_POST['mobile'];
  $address = $_POST['address'];
  $email = $_POST['email'];
  $stmt = $conn->prepare('UPDATE users SET full_name=?, mobile=?, address=?, email=? WHERE id=?');
  $stmt->execute([$full_name, $mobile, $address, $email, $id]);
  $message = 'Account updated!';
}

// Fetch users (exclude admins)
$users = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
// Fetch for edit
$edit = null;
if (isset($_GET['edit'])) {
  $id = intval($_GET['edit']);
  $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
  $stmt->execute([$id]);
  $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Accounts</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(120deg, #fffbe7 0%, #f2f2f2 100%); }
    .centered-card { max-width: 600px; margin: 48px auto 32px auto; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(247,184,1,0.13); padding: 36px 32px 28px 32px; }
    .user-list-card { max-width: 1000px; margin: 0 auto 40px auto; background: #fff; border-radius: 18px; box-shadow: 0 4px 16px rgba(247,184,1,0.10); padding: 32px 24px 36px 24px; }
    h2 { margin-bottom: 18px; color: #F7B801; font-size: 1.5rem; font-weight: 700; text-align: center; }
    form { margin-bottom: 0; }
    input { width: 100%; padding: 14px; margin-bottom: 16px; border: 2px solid #ffe7a0; border-radius: 8px; font-size: 1rem; background: #fff; transition: border 0.2s; }
    input:focus { border: 2px solid #F7B801; outline: none; }
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
    .actions { display: flex; gap: 8px; }
    .actions a { color: #F7B801; text-decoration: none; font-weight: 600; border-radius: 6px; padding: 8px 18px; background: #fffbe7; border: 2px solid #ffe7a0; transition: background 0.2s, color 0.2s; font-size: 1rem; }
    .actions a.delete { color: #fff; background: #c00; border: 2px solid #c00; }
    .actions a:hover { background: #F7B801; color: #fff; }
    .actions a.delete:hover { background: #a00; color: #fff; }
    .back-link { display: inline-block; margin-top: 18px; color: #F7B801; text-decoration: underline; font-weight: 600; }
    .back-link:hover { color: #c00; }
    @media (max-width: 900px) {
      .centered-card, .user-list-card { max-width: 98vw; padding: 18px 2vw; }
    }
    @media (max-width: 600px) {
      .centered-card, .user-list-card { padding: 10px 2vw; }
      th, td { font-size: 0.95rem; }
    }
  </style>
</head>
<body>
  <div class="container">
    <?php if ($edit): ?>
    <div class="centered-card">
      <h2>Edit Account</h2>
      <form method="POST">
        <input type="hidden" name="edit_id" value="<?php echo $edit['id']; ?>">
        <input type="text" name="full_name" placeholder="Full Name" required value="<?php echo htmlspecialchars($edit['full_name']); ?>" />
        <input type="text" name="mobile" placeholder="Mobile Number" required value="<?php echo htmlspecialchars($edit['mobile']); ?>" />
        <input type="text" name="address" placeholder="Address" required value="<?php echo htmlspecialchars($edit['address']); ?>" />
        <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($edit['email']); ?>" />
        <div class="form-btn-row">
          <button type="submit">Update Account</button>
          <a href="accounts.php" class="back-link">Cancel Edit</a>
        </div>
      </form>
      <?php if ($message) echo '<div class="msg">' . htmlspecialchars($message) . '</div>'; ?>
    </div>
    <?php endif; ?>
    
    <div class="user-list-card" style="margin-top: <?php echo $edit ? '25px' : '0'; ?>;">
            <a href="dashboard.php" class="back-link">Back to Dashboard</a>

      <h2>User List</h2>
      <div class="table-wrap">
        <table>
          <tr>
            <th>Full Name</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Email</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
          <?php foreach ($users as $user): ?>
          <tr>
            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
            <td><?php echo htmlspecialchars($user['mobile']); ?></td>
            <td><?php echo htmlspecialchars($user['address']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo $user['created_at']; ?></td>
            <td class="actions">
              <a href="accounts.php?edit=<?php echo $user['id']; ?>">Edit</a>
              <a href="accounts.php?delete=<?php echo $user['id']; ?>" class="delete" onclick="return confirm('Delete this user?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
