<?php
require_once '../includes/db.php';

// Set your desired admin credentials
$full_name = 'Admin User';
$mobile = '09171234567';
$address = 'Admin Office';
$email = 'admin@burpple.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$role = 'admin';

// Check if admin already exists
$stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    echo 'Admin already exists.';
    exit;
}

// Insert admin user
$stmt = $conn->prepare('INSERT INTO users (full_name, mobile, address, email, password, role) VALUES (?, ?, ?, ?, ?, ?)');
if ($stmt->execute([$full_name, $mobile, $address, $email, $password, $role])) {
    echo 'Admin user created successfully!';
} else {
    echo 'Failed to create admin user.';
}
