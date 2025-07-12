<?php
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'customer'; // Default role

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Email already exists.'); window.location.href='register.php';</script>";
        exit;
    }

    // Insert new user with role
    $stmt = $conn->prepare("INSERT INTO users (full_name, mobile, address, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $mobile, $address, $email, $password, $role]);

    echo "<script>alert('Registration successful! Please log in.'); window.location.href='login.php';</script>";
}
?>
