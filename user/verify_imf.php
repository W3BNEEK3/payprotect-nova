<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ ADD THIS BLOCK IMMEDIATELY AFTER $user_id
$checkStmt = $conn->prepare("SELECT is_imf_verified FROM users WHERE id = ?");
$checkStmt->execute([$user_id]);
$checkRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (isset($checkRow['is_imf_verified']) && $checkRow['is_imf_verified'] == 1) {
    header("Location: verify_vat.php");
    exit();
}

// 👇 YOUR EXISTING CODE CONTINUES HERE
$stmt = $conn->prepare("SELECT imf_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$correct_code = $row['imf_code'] ?? '';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_code = trim($_POST['imf_code']);

    if (empty($correct_code)) {
        $error = "No IMF code has been assigned to your account. Please contact Customer Service.";
    } elseif ($entered_code !== $correct_code) {
        $error = "Invalid IMF code. Please contact Customer Service.";
    } else {
        $success = true;

        // ✅ Add this line to mark IMF verified
        $update = $conn->prepare("UPDATE users SET is_imf_verified = 1 WHERE id = ?");
        $update->execute([$user_id]);

        // ✅ Then redirect to the next stage
        header("refresh:2;url=verify_vat.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMF Code Verification</title>
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            padding: 40px;
        }
        .container {
            max-width: 500px;
            background: #fff;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 10px;
            color: #333;
        }
        p.description {
            color: #555;
            font-size: 15px;
            margin-bottom: 25px;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        .btn {
            background-color: #0057a3;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #004080;
        }
        .error {
            color: red;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .contact {
            text-align: center;
            margin-top: 15px;
        }
        .contact a {
            color: #0057a3;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>IMF Code Verification</h2>
        <p class="description">
            Due to strict global financial regulations, the International Monetary Fund (IMF) requires verification for cross-border transactions exceeding limits. This is to prevent fraud and ensure regulatory compliance.
        </p>

        <?php if ($success): ?>
            <p class="success">✅ IMF Code Verified. Redirecting...</p>
        <?php else: ?>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <form method="POST">
                <input type="text" name="imf_code" placeholder="Enter IMF Code" required>
                <button class="btn" type="submit">Verify IMF Code</button>
            </form>
            <div class="contact">
                <p>Need help? <a href="customer_service.php">Contact Customer Service</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
