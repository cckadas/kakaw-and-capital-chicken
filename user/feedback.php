<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback</title>
</head>
<body>
<h2>Send Feedback</h2>
<form action="submit_feedback.php" method="POST">
    <label>Your Feedback:</label><br>
    <textarea name="feedback" rows="4" cols="50" required></textarea><br>

    <label>Rating (1–5):</label>
    <input type="number" name="rating" min="1" max="5" required><br>

    <input type="submit" value="Submit Feedback">
</form>
</body>
</html>
