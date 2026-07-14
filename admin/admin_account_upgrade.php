<?php
session_start();
require_once "../config/config.php"; // Ensure this file exists

// Ensure admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Handle upgrade approval
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $stmt = $conn->prepare("UPDATE users SET is_upgraded = 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    $_SESSION["success"] = "Account successfully upgraded.";
    header("Location: admin_account_upgrade.php");
    exit();
}

// Fetch users pending upgrade
$stmt = $conn->prepare("SELECT id, fullname, email, account_number, currency, balance FROM users WHERE is_upgraded = 0");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Upgrade Requests - Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .container {
            width: 90%;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007bff;
            color: #fff;
        }
        button {
            background-color: #28a745;
            border: none;
            color: #fff;
            padding: 6px 14px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Account Upgrade Requests</h2>

    <?php if (isset($_SESSION["success"])): ?>
        <div class="success"><?php echo $_SESSION["success"]; unset($_SESSION["success"]); ?></div>
    <?php endif; ?>

    <?php if (count($users) === 0): ?>
        <p style="text-align:center;">No users awaiting account upgrade.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Account Number</th>
                <th>Currency</th>
                <th>Balance</th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                    <td><?php echo htmlspecialchars($user['currency']); ?></td>
                    <td><?php echo number_format($user['balance'], 2); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit">Activate</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
