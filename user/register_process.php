<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Test if script is running
echo "Script started...<br>";

try {
    include '../database/db.php';
    echo "Database connection included...<br>";
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

try {
    require '../vendor/autoload.php';
    echo "Autoload included...<br>";
} catch (Exception $e) {
    die("Autoload failed: " . $e->getMessage());
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "Checking request method: " . $_SERVER["REQUEST_METHOD"] . "<br>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $dob = $_POST['dob'];
    $phone = $_POST['phone'];
    $country = $_POST['country'];
    $currency = $_POST['currency'];
    $account_type = $_POST['account_type'];
    $employment = $_POST['employment'];
    $gender = $_POST['gender'];

    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "Email already registered.";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $account_number = rand(1000000000, 9999999999); // 10-digit account number
    $account_status = 'Active';

    $stmt = $conn->prepare("INSERT INTO users 
        (firstname, middlename, lastname, email, password, dob, phone, country, currency, account_type, employment, gender, account_number, account_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $firstname, $middlename, $lastname, $email, $hashed_password, $dob, $phone, $country,
        $currency, $account_type, $employment, $gender, $account_number, $account_status
    ]);

    // After successful user creation, generate verification code
    $verification_code = rand(100000, 999999);
    $verification_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store verification code in database
    $stmt = $conn->prepare("UPDATE users SET verification_code = ?, verification_expiry = ? WHERE email = ?");

    // After storing verification code in database
    $stmt->execute([$verification_code, $verification_expiry, $email]);
    
    // Store email and verification code in session for verification page
    $_SESSION['verify_email'] = $email;
    $_SESSION['verification_code'] = $verification_code;
    
    // Send verification email
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'mail.novartrust.com';
        $mail->SMTPAuth = true;
 
        $mail->Password = 's6rwKreT4tx34ejLETRg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('support@novartrust.com', 'NovaTrust');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Account Verification';
        $mail->Body = "Your verification code is: $verification_code";
        
        if(!$mail->send()) {
            // If email fails, still redirect to verification page
            header("Location: verify_mail.php");
            exit();
        }
        
        // On successful email send
        header("Location: verify_mail.php");
        exit();
    } catch (Exception $e) {
        // On error, still redirect to verification page
        header("Location: verify_mail.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .box h2 {
            color: green;
            margin-bottom: 10px;
        }
        .box p {
            margin-top: 5px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class='box'>
        <h2>Registration Successful!</h2>
        <p>Please check your email for verification code.</p>
    </div>
</body>
</html>
<?php
?>
