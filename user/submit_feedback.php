<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$feedback = trim($_POST['feedback'] ?? '');
$rating = intval($_POST['rating'] ?? 0);
$ip = $_SERVER['REMOTE_ADDR'];

if (!$feedback || $rating < 1 || $rating > 5) {
    echo "Invalid feedback or rating.";
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, feedback_text, rating)
                            VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $feedback, $rating]);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Feedback Submitted</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                margin-top: 50px;
            }
            button {
                padding: 10px 20px;
                font-size: 16px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <h2>Thank you for your feedback!</h2>
        <form action="home.php">
            <button type="submit">Return to Home</button>
        </form>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
