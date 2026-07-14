<?php
session_start();
include '../database/db.php';

// Optional: Protect with admin session check
// if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $imf = $_POST['imf_code'];
    $vat = $_POST['vat_code'];
    $wd  = $_POST['withdrawal_code'];
    $ars = $_POST['ars_code'];

    $stmt = $conn->prepare("UPDATE users SET imf_code = ?, vat_code = ?, withdrawal_code = ?, ars_code = ? WHERE id = ?");
    $stmt->execute([$imf, $vat, $wd, $ars, $user_id]);
    $success = "Codes updated successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Verification Codes</title>
    <style>
        body { font-family: Arial; background: #f2f2f2; margin: 0; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        h2 { text-align: center; }
        form { display: inline; }
        input[type="text"] { width: 100px; padding: 5px; }
        input[type="submit"] { padding: 6px 10px; background: #0066cc; color: #fff; border: none; cursor: pointer; border-radius: 4px; }
        input[type="submit"]:hover { background: #004999; }
        .success { background: #dff0d8; padding: 10px; border-left: 5px solid green; margin-bottom: 20px; }
    </style>
</head>
<body>

<h2>🛡️ Manage User Verification Codes</h2>

<?php if (isset($success)) echo "<div class='success'>$success</div>"; ?>

<table>
    <tr>
        <th>User ID</th>
        <th>Name</th>
        <th>IMF</th>
        <th>VAT</th>
        <th>Withdrawal Code</th>
        <th>ARS Token</th>
        <th>Action</th>
    </tr>

    <?php
    $stmt = $conn->query("SELECT id, firstname, imf_code, vat_code, withdrawal_code, ars_code FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <form method='POST'>
            <td>{$row['id']}<input type='hidden' name='user_id' value='{$row['id']}'></td>
            <td>{$row['firstname']}</td>
            <td><input type='text' name='imf_code' value='{$row['imf_code']}'></td>
            <td><input type='text' name='vat_code' value='{$row['vat_code']}'></td>
            <td><input type='text' name='withdrawal_code' value='{$row['withdrawal_code']}'></td>
            <td><input type='text' name='ars_code' value='{$row['ars_code']}'></td>
            <td><input type='submit' value='Update'></td>
            </form>
        </tr>";
    }
    ?>
</table>

</body>
</html>
