<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$errors = [];
$success = '';
$adminName = $_SESSION['admin_name'] ?? 'Administrator';

// Fetch reusable data
$usersStmt = $conn->query("SELECT id, firstname, lastname, email FROM users ORDER BY created_at DESC");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
$selectedUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : ($users[0]['id'] ?? null);

$allRequirementsStmt = $conn->query("SELECT * FROM compliance_requirements ORDER BY created_at DESC");
$allRequirements = $allRequirementsStmt->fetchAll(PDO::FETCH_ASSOC);
$activeRequirements = array_values(array_filter($allRequirements, fn($req) => $req['is_active']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_requirement') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $fee = trim($_POST['fee_amount'] ?? '0');

        if ($name === '') {
            $errors[] = 'Compliance name is required.';
        }
        if ($fee !== '' && !is_numeric($fee)) {
            $errors[] = 'Fee amount must be numeric.';
        }

        if (!$errors) {
            $stmt = $conn->prepare("INSERT INTO compliance_requirements (name, description, fee_amount, is_active) VALUES (?, ?, ?, 1)");
            $stmt->execute([$name, $description, $fee === '' ? 0 : floatval($fee)]);
            $success = 'Compliance requirement created successfully.';
        }
    } elseif ($action === 'toggle_requirement') {
        $requirementId = intval($_POST['requirement_id'] ?? 0);
        $current = intval($_POST['current_state'] ?? 0);
        $newState = $current ? 0 : 1;

        $stmt = $conn->prepare("UPDATE compliance_requirements SET is_active = ? WHERE id = ?");
        $stmt->execute([$newState, $requirementId]);
        $success = $newState ? 'Requirement activated.' : 'Requirement archived.';
    } elseif ($action === 'assign_code') {
        $userId = intval($_POST['user_id'] ?? 0);
        $selectedUserId = $userId;
        $requirementId = intval($_POST['compliance_id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$userId || !$requirementId) {
            $errors[] = 'User and compliance requirement are required.';
        }
        if ($code === '') {
            $errors[] = 'Please enter or generate a code before assigning it.';
        }

        if (!$errors) {
            $stmt = $conn->prepare("INSERT INTO user_compliance_codes (user_id, compliance_id, code, notes, assigned_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $requirementId, $code, $notes, $adminName]);
            $success = 'Compliance code assigned to user.';
        }
    } elseif ($action === 'mark_cleared') {
        $assignmentId = intval($_POST['assignment_id'] ?? 0);
        $selectedUserId = intval($_POST['user_id'] ?? $selectedUserId);

        if ($assignmentId) {
            $stmt = $conn->prepare("UPDATE user_compliance_codes SET is_cleared = 1, cleared_at = NOW() WHERE id = ?");
            $stmt->execute([$assignmentId]);
            $success = 'Compliance marked as cleared for this user.';
        }
    } elseif ($action === 'delete_assignment') {
        $assignmentId = intval($_POST['assignment_id'] ?? 0);
        $selectedUserId = intval($_POST['user_id'] ?? $selectedUserId);

        if ($assignmentId) {
            $stmt = $conn->prepare("DELETE FROM user_compliance_codes WHERE id = ?");
            $stmt->execute([$assignmentId]);
            $success = 'Compliance assignment removed.';
        }
    }

    // Refresh requirement caches so UI reflects changes immediately
    $allRequirementsStmt = $conn->query("SELECT * FROM compliance_requirements ORDER BY created_at DESC");
    $allRequirements = $allRequirementsStmt->fetchAll(PDO::FETCH_ASSOC);
    $activeRequirements = array_values(array_filter($allRequirements, fn($req) => $req['is_active']));
}

$assignments = [];
$pendingCount = 0;
$clearedCount = 0;

if ($selectedUserId) {
    $assignStmt = $conn->prepare("SELECT uc.*, cr.name AS compliance_name FROM user_compliance_codes uc JOIN compliance_requirements cr ON uc.compliance_id = cr.id WHERE uc.user_id = ? ORDER BY uc.assigned_at DESC");
    $assignStmt->execute([$selectedUserId]);
    $assignments = $assignStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($assignments as $row) {
        if ($row['is_cleared']) {
            $clearedCount++;
        } else {
            $pendingCount++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compliance Management</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f6fb; margin: 0; padding: 30px; }
        h1 { text-align: center; color: #1f3b67; margin-bottom: 10px; }
        p.subtitle { text-align: center; color: #5f6c7b; margin-bottom: 30px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 25px rgba(31,59,103,0.08); padding: 25px; margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
        label { font-weight: 600; color: #334155; display: block; margin-bottom: 6px; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5f5; font-size: 15px; }
        textarea { min-height: 90px; resize: vertical; }
        button, .btn { background: linear-gradient(135deg,#4f46e5,#9333ea); color: #fff; border: none; border-radius: 8px; padding: 12px 18px; font-size: 15px; cursor: pointer; transition: opacity .2s; }
        button:hover, .btn:hover { opacity: .9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 14px; border-bottom: 1px solid #eef1f7; text-align: left; }
        th { background: #f0f2ff; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; }
        .status-badge { padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-active { background: #e0f7ef; color: #0f9d58; }
        .status-inactive { background: #fde8e8; color: #c53030; }
        .status-pending { background: #fff6e5; color: #b45309; }
        .status-cleared { background: #e0f7ef; color: #0f9d58; }
        .flash { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .flash-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .flash-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        form.inline { display: inline; }
        .actions form { display: inline-block; margin-right: 6px; }
    </style>
</head>
<body>
    <h1>Compliance Management</h1>
    <p class="subtitle">Define compliance requirements and assign individual codes to regulate user withdrawals.</p>

    <?php if ($success): ?>
        <div class="flash flash-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="flash flash-error">
            <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2 style="margin-top:0; color:#1d3557;">Select User</h2>
        <?php if (!$users): ?>
            <p>No users available.</p>
        <?php else: ?>
            <form method="GET" style="margin-bottom: 0;">
                <label for="user_id">Choose a user</label>
                <select id="user_id" name="user_id" onchange="this.form.submit()">
                    <?php foreach ($users as $user): ?>
                        <?php $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')); ?>
                        <option value="<?= $user['id'] ?>" <?= ($user['id'] == $selectedUserId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fullName ?: $user['email']) ?> (<?= htmlspecialchars($user['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div style="margin-top: 15px; display: flex; gap: 15px; flex-wrap: wrap;">
                <span class="status-badge status-pending">Pending: <?= $pendingCount ?></span>
                <span class="status-badge status-cleared">Cleared: <?= $clearedCount ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 style="margin-top:0; color:#1d3557;">Assign Compliance to User</h2>
        <?php if (!$selectedUserId): ?>
            <p>You must select a user first.</p>
        <?php elseif (!$activeRequirements): ?>
            <p>No active compliance requirements. Create one below to begin assigning codes.</p>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="action" value="assign_code">
                <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
                <div class="grid">
                    <div>
                        <label for="compliance_id">Compliance Type</label>
                        <select id="compliance_id" name="compliance_id" required>
                            <option value="">-- Select compliance --</option>
                            <?php foreach ($activeRequirements as $req): ?>
                                <option value="<?= $req['id'] ?>"><?= htmlspecialchars($req['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="code">Code</label>
                        <input type="text" id="code" name="code" placeholder="Enter or paste the assigned code" required>
                    </div>
                </div>
                <div style="margin-top:18px;">
                    <label for="notes">Notes (optional)</label>
                    <textarea id="notes" name="notes" placeholder="Add any internal notes or payment instructions"></textarea>
                </div>
                <div style="margin-top:20px; display:flex; justify-content:flex-end;">
                    <button type="submit">Assign Compliance</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 style="margin-top:0; color:#1d3557;">User Compliance Assignments</h2>
        <?php if (!$selectedUserId || !$assignments): ?>
            <p>No compliances have been assigned to this user yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Compliance</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Cleared</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?= htmlspecialchars($assignment['compliance_name']) ?></td>
                        <td><?= htmlspecialchars($assignment['code']) ?></td>
                        <td>
                            <span class="status-badge <?= $assignment['is_cleared'] ? 'status-cleared' : 'status-pending' ?>">
                                <?= $assignment['is_cleared'] ? 'Cleared' : 'Pending' ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y H:i', strtotime($assignment['assigned_at'])) ?></td>
                        <td><?= $assignment['cleared_at'] ? date('M d, Y H:i', strtotime($assignment['cleared_at'])) : '—' ?></td>
                        <td><?= $assignment['notes'] ? htmlspecialchars($assignment['notes']) : '—' ?></td>
                        <td class="actions">
                            <?php if (!$assignment['is_cleared']): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="mark_cleared">
                                    <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                    <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
                                    <button type="submit" class="btn" style="background:#0f9d58;">Mark Cleared</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Remove this compliance assignment?');">
                                <input type="hidden" name="action" value="delete_assignment">
                                <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
                                <button type="submit" class="btn" style="background:#ef4444;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 style="margin-top:0; color:#1d3557;">Add New Compliance</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_requirement">
            <div class="grid">
                <div>
                    <label for="name">Compliance Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g., International Monetary Fund Clearance" required>
                </div>
                <div>
                    <label for="fee_amount">Fee Amount (optional)</label>
                    <input type="number" step="0.01" id="fee_amount" name="fee_amount" placeholder="0.00">
                </div>
            </div>
            <div style="margin-top:18px;">
                <label for="description">Description / Instructions</label>
                <textarea id="description" name="description" placeholder="Describe why this compliance is needed and how codes are generated."></textarea>
            </div>
            <div style="margin-top:20px; display:flex; justify-content:flex-end;">
                <button type="submit">Create Compliance</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-top:0; color:#1d3557;">Existing Requirements</h2>
        <?php if (!$allRequirements): ?>
            <p>No compliance requirements have been defined yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($allRequirements as $req): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($req['name']) ?></strong><br>
                            <small style="color:#94a3b8;><?= $req['description'] ? htmlspecialchars($req['description']) : 'No description provided' ?></small>
                        </td>
                        <td><?= number_format((float)$req['fee_amount'], 2) ?></td>
                        <td>
                            <span class="status-badge <?= $req['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $req['is_active'] ? 'Active' : 'Archived' ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                        <td>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="toggle_requirement">
                                <input type="hidden" name="requirement_id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="current_state" value="<?= $req['is_active'] ?>">
                                <button type="submit">
                                    <?= $req['is_active'] ? 'Archive' : 'Activate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
