<?php
session_start();
require_once(__DIR__ . "/../config/config.php");

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Handle upgrade approval
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("UPDATE users SET account_type = 'upgraded' WHERE id = ?");
    $stmt->execute([$user_id]);
    $_SESSION['success'] = "User account upgraded successfully.";
    header("Location: admin_account_upgrade.php");
    exit();
}

// Fetch users not yet upgraded
$stmt = $conn->query("SELECT id, fullname, email, account_number FROM users WHERE account_type != 'upgraded'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Upgrade Approvals</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        table { width: 100%; background: white; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        button { background: blue; color: white; padding: 6px 12px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>🔐 Account Upgrade Approvals</h2>
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (count($users) > 0): ?>
        <table>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Account Number</th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit">Approve Upgrade</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No users pending upgrade approval.</p>
    <?php endif; ?>
</body>
</html>
