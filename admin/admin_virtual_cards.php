<?php
require_once '../config/config.php';

// Get all virtual cards and their users
$stmt = $conn->query("
    SELECT 
        u.id AS user_id,
        u.fullname,
        u.email,
        v.id AS card_id,
        v.is_virtual_card_approved,
        v.status
    FROM users u
    LEFT JOIN virtual_cards v ON u.id = v.user_id
    ORDER BY u.id DESC
");
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Virtual Card Approvals</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 40px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #004080;
            color: white;
        }
        .btn {
            padding: 6px 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn:hover {
            background: #218838;
        }
        .no-data {
            text-align: center;
            margin-top: 50px;
            font-size: 18px;
            color: #777;
        }
    </style>
</head>
<body>
    <h2>Pending Virtual Card Requests</h2>

    <?php if (count($cards) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cards as $card): ?>
                    <tr>
                        <td><?= htmlspecialchars($card['user_id']) ?></td>
                        <td><?= htmlspecialchars($card['fullname']) ?></td>
                        <td><?= htmlspecialchars($card['email']) ?></td>
                        <td>
                            <?php if ($card['is_virtual_card_approved'] == 1 && $card['status'] !== 'Blocked'): ?>
                                <span style='color:green;'>✅ Approved</span>
                            <?php elseif ($card['status'] === 'Blocked'): ?>
                                <span style='color:red;'>❌ Blocked</span>
                            <?php else: ?>
                                Pending
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$card['is_virtual_card_approved']): ?>
                                <a class="btn" href="approve_virtual_card.php?user_id=<?= $card['user_id'] ?>">Approve</a>
                            <?php elseif ($card['is_virtual_card_approved'] == 1 && $card['status'] !== 'Blocked'): ?>
                                <a class="btn" style="background:#dc2626;" href="block_virtual_card.php?card_id=<?= $card['card_id'] ?>">Block</a>
                            <?php elseif ($card['status'] === 'Blocked'): ?>
                                <span style='color:#dc2626;'>Blocked</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No pending virtual card request found.</p>
    <?php endif; ?>
</body>
</html>
