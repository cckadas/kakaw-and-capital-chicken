<?php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// Handle update
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_mode']) && $_POST['edit_mode'] === '1') {
  $full_name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $mobile = trim($_POST['mobile'] ?? '');
  $address = trim($_POST['address'] ?? '');
  if ($full_name && $email && $mobile && $address) {
    try {
      $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, mobile=?, address=? WHERE id=?");
      $stmt->execute([$full_name, $email, $mobile, $address, $_SESSION['user_id']]);
      $success = true;
    } catch (PDOException $e) {
      if ($e->getCode() == 23000) {
        $error = 'Email already exists.';
      } else {
        $error = 'Update failed.';
      }
    }
  } else {
    $error = 'All fields are required.';
  }
}
// Fetch user info (again after update)
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Account - Kakaw & Capital Chicken</title>
  <style>
    body { background: #f2f2f2; font-family: 'Segoe UI', sans-serif; }
    .account-container {
      max-width: 420px; margin: 40px auto; background: #fff; border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 32px 24px;
    }
    h2 { color: #F7B801; margin-bottom: 18px; }
    .info-row { margin-bottom: 14px; }
    .label { color: #888; font-size: 14px; }
    .value { font-size: 16px; font-weight: 500; color: #333; }
    .edit-btn, .save-btn, .cancel-btn {
      background: #F7B801; color: #fff; border: none; border-radius: 5px;
      padding: 7px 16px; font-size: 14px; font-weight: 500; cursor: pointer;
      margin-top: 18px; margin-right: 8px;
    }
    .edit-btn:hover, .save-btn:hover, .cancel-btn:hover { background: #e0a700; }
    .form-group { margin-bottom: 16px; }
    input, textarea {
      width: 100%; padding: 8px 10px; border: 1px solid #e0e0e0; border-radius: 5px;
      font-size: 15px; margin-top: 4px; background: #fafafa;
    }
    textarea { resize: vertical; min-height: 60px; }
    .msg-success { color: #2e7d32; background: #eafbe7; border-radius: 5px; padding: 8px 12px; margin-bottom: 12px; }
    .msg-error { color: #b71c1c; background: #fdeaea; border-radius: 5px; padding: 8px 12px; margin-bottom: 12px; }
    .btn-row { display: flex; gap: 10px; }
  </style>
  <script>
    function enableEdit() {
      document.getElementById('edit_mode').value = '1';
      document.querySelectorAll('.edit-field').forEach(e => e.removeAttribute('readonly'));
      document.getElementById('editBtn').style.display = 'none';
      document.getElementById('saveBtn').style.display = 'inline-block';
      document.getElementById('cancelBtn').style.display = 'inline-block';
    }
    function cancelEdit() {
      window.location.reload();
    }
  </script>
</head>
<body>
  <div class="account-container">
    <h2>My Account</h2>
    <a href="home.php" style="display:inline-block;margin-bottom:18px;color:#F7B801;text-decoration:underline;font-weight:600;">&larr; Home</a>
    <?php if ($success): ?>
      <div class="msg-success">Account updated successfully.</div>
    <?php elseif ($error): ?>
      <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
      <input type="hidden" name="edit_mode" id="edit_mode" value="0">
      <div class="form-group">
        <label class="label">Full Name</label>
        <input type="text" class="edit-field" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly required>
      </div>
      <div class="form-group">
        <label class="label">Email</label>
        <input type="email" class="edit-field" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly required>
      </div>
      <div class="form-group">
        <label class="label">Mobile Number</label>
        <input type="text" class="edit-field" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" readonly required>
      </div>
      <div class="form-group">
        <label class="label">Address</label>
        <textarea class="edit-field" name="address" readonly required><?php echo htmlspecialchars($user['address']); ?></textarea>
      </div>
      <div class="btn-row">
        <button type="button" class="edit-btn" id="editBtn" onclick="enableEdit()">Update</button>
        <button type="submit" class="save-btn" id="saveBtn" style="display:none;">Save</button>
        <button type="button" class="cancel-btn" id="cancelBtn" style="display:none;" onclick="cancelEdit()">Cancel</button>
      </div>
    </form>
  </div>
</body>
</html>
