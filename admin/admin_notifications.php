<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all notifications (latest first)
$stmt = $conn->query("
    SELECT notifications.*, users.fullname 
    FROM notifications 
    JOIN users ON notifications.user_id = users.id 
    ORDER BY notifications.created_at DESC
");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Notifications - NovaTrust Admin</title>
    <style>
        body {
            background-color: #eef2f5;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 15px;
        }

        th {
            background-color: #004080;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .type-alert {
            color: #2e7d32;
            font-weight: bold;
        }

        .type-info {
            color: #1565c0;
            font-weight: bold;
        }

        .type-warning {
            color: #e65100;
            font-weight: bold;
        }

        .timestamp {
            font-size: 13px;
            color: #555;
        }

        .back-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #004080;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .back-btn:hover {
            background-color: #003060;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📢 All User Notifications</h2>

    <table>
        <tr>
            <th>User</th>
            <th>Title</th>
            <th>Message</th>
            <th>Type</th>
            <th>Date</th>
        </tr>

        <?php if (count($notifications) === 0): ?>
            <tr><td colspan="5">No notifications sent yet.</td></tr>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <tr>
                    <td><?php echo htmlspecialchars($n['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($n['title']); ?></td>
                    <td><?php echo htmlspecialchars($n['message']); ?></td>
                    <td class="type-<?php echo htmlspecialchars($n['type']); ?>"><?php echo ucfirst($n['type']); ?></td>
                    <td class="timestamp"><?php echo date('M j, Y • h:i A', strtotime($n['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <a class="back-btn" href="admin_dashboard.php">← Back to Admin Dashboard</a>
</div>

</body>
</html>
