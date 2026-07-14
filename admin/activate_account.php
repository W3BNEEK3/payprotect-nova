<?php
require_once "../database/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];
    $stmt = $conn->prepare("UPDATE users SET account_status = 'active' WHERE id = ?");
    $stmt->execute([$user_id]);

    header("Location: admin_suspend.php");
    exit();
}
?>
