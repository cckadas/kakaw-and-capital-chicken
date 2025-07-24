<?php
session_start();
require_once '../includes/db.php';

$stmt = $conn->query("SELECT f.*, u.full_name FROM feedback f
                     LEFT JOIN users u ON f.user_id = u.id
                     ORDER BY f.submitted_at DESC");
$rows = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback Dashboard</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background-color: #eee; }
    </style>
</head>
<body>
<h2>All Feedback</h2>
<table>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Rating</th>
        <th>Feedback</th>
        <th>Date</th>
    </tr>
    <?php foreach ($rows as $row): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['full_name'] ?? 'Guest') ?></td>
        <td><?= $row['rating'] ?></td>
        <td><?= htmlspecialchars($row['feedback_text']) ?></td>
        <td><?= $row['submitted_at'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>