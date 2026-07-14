// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE account_number = ?");
$stmt->execute([$account_number]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $user_id = $user['id'];

    // 1. Insert refund
    $stmt = $conn->prepare("INSERT INTO refunds (user_id, account_number, amount, reason, refunded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $account_number, $amount, $reason, $refunded_by]);

    // 2. Update refund_balance
    $stmt = $conn->prepare("UPDATE users SET refund_balance = refund_balance + ?, balance = balance + ? WHERE id = ?");
    $stmt->execute([$amount, $amount, $user_id]);

    // 3. Insert into transaction history
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'refund', ?, 'Refund credited by admin')");
    $stmt->execute([$user_id, $amount]);

    header("Location: refund_receipt.php?success=1");
    exit();
}
