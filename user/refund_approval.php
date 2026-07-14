<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['refund_id'])) {
    $refund_id = $_POST['refund_id'];
    $stmt = $conn->prepare("UPDATE refunds SET status = 'approved' WHERE id = ?");
    if ($stmt->execute([$refund_id])) {
        echo "Refund Approved Successfully.";
    } else {
        echo "Error approving refund.";
    }
    exit();
}

// Fetch pending refunds
$stmt = $conn->prepare("SELECT r.id, r.amount, r.reason, u.email FROM refunds r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending'");
$stmt->execute();
$refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Refund Approvals</title>
    <style>
        body { font-family: Arial; background: #f0f8ff; padding: 20px; }
        h2 { color: #005f8f; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background: #007BFF; color: white; }
        form { display: inline; }
        .btn { background-color: #28a745; color: white; padding: 5px 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
<h2>Pending Refund Requests</h2>
<table>
    <tr>
        <th>User Email</th><th>Amount</th><th>Reason</th><th>Action</th>
    </tr>
    <?php foreach ($refunds as $refund): ?>
    <tr>
        <td><?= htmlspecialchars($refund['email']) ?></td>
        <td>$<?= htmlspecialchars($refund['amount']) ?></td>
        <td><?= htmlspecialchars($refund['reason']) ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="refund_id" value="<?= $refund['id'] ?>">
                <button class="btn">Approve</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>