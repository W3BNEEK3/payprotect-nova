<?php
session_start();
require_once "../database/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin["password"])) {
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_name"] = $admin["fullname"];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION["error"] = "Invalid login credentials.";
        header("Location: admin_login.php");
        exit();
    }
}
?>
