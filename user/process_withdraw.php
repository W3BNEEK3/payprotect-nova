<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$returnUrl = $_SESSION['withdraw_return_url'] ?? 'withdraw.php';

$pendingStmt = $conn->prepare("SELECT uc.*, cr.name, cr.description FROM user_compliance_codes uc JOIN compliance_requirements cr ON uc.compliance_id = cr.id WHERE uc.user_id = ? AND uc.is_cleared = 0 ORDER BY uc.assigned_at ASC LIMIT 1");
$pendingStmt->execute([$user_id]);
$currentCompliance = $pendingStmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$currentCompliance) {
        header("Location: $returnUrl");
        exit();
    }

    $enteredCode = trim($_POST['code'] ?? '');
    if ($enteredCode === '') {
        $error = 'Please enter the code that was assigned to you.';
    } elseif (strcasecmp($enteredCode, $currentCompliance['code']) !== 0) {
        $error = 'The code entered does not match our records. Please contact support if you need assistance.';
    } else {
        $update = $conn->prepare("UPDATE user_compliance_codes SET is_cleared = 1, cleared_at = NOW() WHERE id = ?");
        $update->execute([$currentCompliance['id']]);
        $_SESSION['compliance_success'] = $currentCompliance['name'] . ' cleared successfully.';

        $nextPendingStmt = $conn->prepare("SELECT COUNT(*) FROM user_compliance_codes WHERE user_id = ? AND is_cleared = 0");
        $nextPendingStmt->execute([$user_id]);
        $remaining = (int)$nextPendingStmt->fetchColumn();

        if ($remaining === 0) {
            header("Location: virtual_card.php?flow=withdrawal");
        } else {
            header("Location: $returnUrl");
        }
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compliance Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 45%, #fff 100%);
            margin: 0;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        .background-shapes {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }
        .shape {
            position: absolute;
            border-radius: 999px;
            opacity: 0.25;
            filter: blur(0px);
        }
        .shape-one {
            width: 420px;
            height: 420px;
            background: #a5b4fc;
            top: -120px;
            left: -80px;
        }
        .shape-two {
            width: 260px;
            height: 260px;
            background: #fcd34d;
            bottom: 10%;
            left: -60px;
            opacity: 0.2;
        }
        .shape-three {
            width: 340px;
            height: 340px;
            background: #34d399;
            top: 15%;
            right: -120px;
            opacity: 0.18;
        }
        .shape-four {
            width: 160px;
            height: 160px;
            background: #f472b6;
            bottom: -40px;
            right: 12%;
            opacity: 0.28;
        }
        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            background: #fff;
            border-radius: 20px;
            padding: 40px 48px;
            box-shadow: 0 25px 70px rgba(15,37,64,0.12);
        }
        h2 {
            margin-top: 0;
            margin-bottom: 12px;
            color: #1f3b67;
            font-size: 1.75rem;
        }
        p {
            color: #4b5563;
            line-height: 1.6;
            font-size: 1rem;
        }
        .empty-state {
            text-align: center;
            padding: 32px 28px;
            border: 1px dashed #cdd5f7;
            border-radius: 16px;
            background: rgba(249, 251, 255, 0.9);
        }
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 22px;
            font-weight: 600;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1f2937;
        }
        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            font-size: 17px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            margin-bottom: 18px;
        }
        button {
            width: 100%;
            padding: 14px 20px;
            font-size: 17px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg,#4f46e5,#9333ea);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.25);
        }
        button:hover {
            opacity: .94;
        }
        .support {
            text-align: center;
            margin-top: 18px;
        }
        .support a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 640px) {
            body {
                padding: 16px;
            }
            .card {
                padding: 30px 22px;
                border-radius: 18px;
            }
            h2 {
                font-size: 1.45rem;
            }
            p {
                font-size: 0.96rem;
            }
            input[type="text"],
            button {
                font-size: 16px;
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <span class="shape shape-one"></span>
        <span class="shape shape-two"></span>
        <span class="shape shape-three"></span>
        <span class="shape shape-four"></span>
    </div>
    <div class="card">
        <?php if (!$currentCompliance): ?>
            <div class="empty-state">
                <h3>No Compliance Code Assigned Yet</h3>
                <p>Your account currently has no pending compliance codes. Please contact support so an agent can assign the required authorization to your profile.</p>
                <p class="support"><a href="customer_service.php">Contact Support</a></p>
            </div>
        <?php else: ?>
            <h2><?= htmlspecialchars($currentCompliance['name']) ?> Verification</h2>
            <p><?= $currentCompliance['description'] ? htmlspecialchars($currentCompliance['description']) : 'Please provide the code supplied by your compliance officer to proceed.' ?></p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="code">Enter Assigned Code</label>
                <input type="text" id="code" name="code" placeholder="e.g., IMF-2394" required>
                <button type="submit">Verify Code</button>
            </form>

            <p class="support">Need help? <a href="customer_service.php">Contact Support</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
