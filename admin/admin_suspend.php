<?php
session_start();
require_once "../database/db.php";

// Ensure only admin can access
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all users with 'suspended' status
$stmt = $conn->prepare("SELECT id, firstname, lastname, account_number, account_status FROM users WHERE account_status = 'suspended'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Suspend/Activate Accounts - NovaTrust Admin</title>
    <style>
        body {
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #fff;
            padding: 20px;
            margin-top: 40px;
            box-shadow: 0 0 10px #ccc;
        }
        h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        .btn {
            padding: 8px 12px;
            background: green;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: darkgreen;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Suspended Accounts</h2>

    <?php if (count($users) > 0): ?>
        <table>
            <tr>
                <th>Account Number</th>
                <th>Full Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                    <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($user['account_status']); ?></td>
                    <td>
                        <form method="post" action="activate_account.php">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn">Activate</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No suspended accounts found.</p>
    <?php endif; ?>
</div>
</body>
</html>
