<?php
session_start();
require_once '../includes/db.php';


// Auto-create default admin if not exists
$default_admin_email = 'admin@gmail.com';
$stmt = $conn->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
$stmt->execute([$default_admin_email, 'admin']);
if ($stmt->rowCount() === 0) {
    $full_name = 'Admin User';
    $mobile = '09171234567';
    $address = 'Admin Office';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $role = 'admin';
    $insert_stmt = $conn->prepare('INSERT INTO users (full_name, mobile, address, email, password, role) VALUES (?, ?, ?, ?, ?, ?)');
    $insert_stmt->execute([$full_name, $mobile, $address, $default_admin_email, $password, $role]);
}

if (isset($_SESSION['admin_id'])) {
  header('Location: dashboard.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
  $stmt->execute([$email, 'admin']);
  $admin = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['full_name'];
    header('Location: dashboard.php');
    exit;
  } else {
    $error = 'Invalid credentials.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f2f2f2; display: flex; justify-content: center; align-items: center; height: 100vh; }
    .login-box { background: #fff; padding: 32px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 100%; max-width: 350px; }
    h2 { text-align: center; margin-bottom: 20px; }
    input { width: 100%; padding: 12px; margin-bottom: 14px; border: 1px solid #e0e0e0; border-radius: 6px; }
    button { width: 100%; padding: 12px; background: #F7B801; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
    .error { color: #c00; text-align: center; margin-bottom: 10px; }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Admin Login</h2>
    <?php if (!empty($error)) echo '<div class="error">'.$error.'</div>'; ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Admin Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Log In</button>
    </form>
  </div>
</body>
</html>
