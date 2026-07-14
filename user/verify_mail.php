<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
    <div class="card">
        <div class="card-header" style="background-color: #800000; color: white;">
            <h3>Secure Email Verification</h3>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <p>For your security, we've sent a 6-digit verification code to your registered email address.</p>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label>Verification Code</label>
                    <input type="text" name="verification_code" class="form-control" required 
                           pattern="\d{6}" title="6-digit code" maxlength="6">
                </div>
                <button type="submit" class="btn btn-primary">Verify Account</button>
            </form>
            <p class="text-muted mt-3">Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
        </div>
    </div>
</div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Enter verification code sent to your email:</label>
                <input type="text" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Verify Email</button>
        </form>
        <p>Didn't receive a code? <a href="#" id="resend-link">Resend verification email</a></p>
    </div>

    <script>
        document.getElementById('resend-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('resend_verification.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Verification email resent successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Confirmation - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            color: #333;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #800000;
        }
        p {
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            background: maroon;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Delivery Confirmation</h2>
        <p>To ensure the security of all transaction alerts and account communications, we require confirmation of automated mail routing.</p>
        <form method="POST">
            <button type="submit">Confirm Mail Delivery</button>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once '../database/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $user_id = $_SESSION['user_id'];
    $verification_code = $_POST['verification_code'];

    // Check verification code
    $stmt = $conn->prepare("SELECT verification_code, verification_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] == $verification_code) {
        if (strtotime($user['verification_expiry']) > time()) {
            // Code is valid and not expired
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = "Email verified successfully!";
            $_SESSION['mail_verified'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Verification code has expired.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif;