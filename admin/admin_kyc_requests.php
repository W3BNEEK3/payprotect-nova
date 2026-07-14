<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all users with or without KYC codes
$stmt = $conn->query("SELECT id, firstname, lastname, account_number, email, kyc_code FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate new code
function generateKycCode() {
    return 'KYC-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>KYC Code Approvals</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
        }
        h2 {
            color: #004085;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        table th, table td {
            padding: 14px;
            border: 1px solid #ccc;
            text-align: left;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        button {
            padding: 7px 14px;
            background-color: #28a745;
            border: none;
            color: white;
            cursor: pointer;
        }
        input[readonly] {
            background-color: #e9ecef;
            border: none;
            padding: 6px;
            width: 160px;
        }
    </style>
</head>
<body>

<h2>KYC Code Approvals</h2>

<table>
    <thead>
        <tr>
            <th>Full Name</th>
            <th>Account Number</th>
            <th>Email</th>
            <th>KYC Code</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <form method="post" action="admin_kyc_process.php">
                <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <input type="text" name="kyc_code" 
                        value="<?php echo $user['kyc_code'] ? $user['kyc_code'] : generateKycCode(); ?>" 
                        readonly>
                </td>
                <td>
                    <?php if (!$user['kyc_code']): ?>
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit">Approve</button>
                    <?php else: ?>
                        ✅ Approved
                    <?php endif; ?>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
