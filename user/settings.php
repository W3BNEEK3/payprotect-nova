<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $phone = $_POST['phone'];
    $country = $_POST['country'];
    $currency = $_POST['currency'];
    $employment = $_POST['employment'];

    $stmt = $conn->prepare("UPDATE users SET phone = ?, country = ?, currency = ?, employment = ? WHERE id = ?");
    $stmt->execute([$phone, $country, $currency, $employment, $user_id]);
    $success = "Profile updated successfully!";
}

// Country and Currency Arrays
$countries = ["Afghanistan","Albania","Algeria","Andorra","Angola","Argentina","Armenia","Australia","Austria","Azerbaijan",
"Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bhutan","Bolivia","Bosnia & Herzegovina",
"Botswana","Brazil","Brunei","Bulgaria","Burkina Faso","Burundi","Cabo Verde","Cambodia","Cameroon","Canada","Chad","Chile",
"China","Colombia","Comoros","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Dominica","Dominican Republic",
"Ecuador","Egypt","El Salvador","Estonia","Ethiopia","Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana",
"Greece","Grenada","Guatemala","Guinea","Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq",
"Ireland","Israel","Italy","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Kuwait","Kyrgyzstan","Laos","Latvia",
"Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Madagascar","Malawi","Malaysia","Maldives",
"Mali","Malta","Mauritania","Mauritius","Mexico","Moldova","Monaco","Mongolia","Montenegro","Morocco","Mozambique","Myanmar",
"Namibia","Nepal","Netherlands","New Zealand","Nicaragua","Niger","Nigeria","North Korea","North Macedonia","Norway","Oman",
"Pakistan","Palau","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Qatar","Romania","Russia",
"Rwanda","Saint Lucia","Samoa","San Marino","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia",
"Slovenia","Somalia","South Africa","South Korea","Spain","Sri Lanka","Sudan","Suriname","Sweden","Switzerland","Syria","Taiwan",
"Tajikistan","Tania","Thailand","Togo","Tonga","Trinidad & Tobago","Tunisia","Turkey","Turkmenistan","Uganda","Ukraine",
"United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"];

$currencies = ["USD - US Dollar","EUR - Euro","GBP - British Pound","NGN - Nigerian Naira","INR - Indian Rupee","JPY - Japanese Yen",
"AUD - Australian Dollar","CAD - Canadian Dollar","CHF - Swiss Franc","CNY - Chinese Yuan","ZAR - South African Rand","BRL - Brazilian Real",
"SEK - Swedish Krona","NOK - Norwegian Krone","RUB - Russian Ruble","IDR - Indonesian Rupiah","KES - Kenyan Shilling","GHS - Ghanaian Cedi",
"TZS - Tanzanian Shilling","UAH - Ukrainian Hryvnia","PKR - Pakistani Rupee","BDT - Bangladeshi Taka","LKR - Sri Lankan Rupee","MXN - Mexican Peso"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings - NovaTrust</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/user-styles.css">
</head>
<body>
<div class="settings-container">
    <h2 class="settings-title">⚙️ Account Settings</h2>
    <?php if (isset($success)) echo "<div class='success-message'>$success</div>"; ?>

    <form method="POST" class="settings-form">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-input" value="<?php echo $user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']; ?>" disabled>

        <label class="form-label">Email</label>
        <input type="email" class="form-input" value="<?php echo $user['email']; ?>" disabled>

        <label class="form-label">Phone Number</label>
        <input type="text" class="form-input" name="phone" value="<?php echo $user['phone']; ?>" required>

        <label class="form-label">Gender</label>
        <input type="text" class="form-input" value="<?php echo $user['gender']; ?>" disabled>

        <label class="form-label">Date of Birth</label>
        <input type="text" class="form-input" value="<?php echo $user['dob']; ?>" disabled>

        <label class="form-label">Country</label>
        <select name="country" class="form-select" required>
            <?php foreach ($countries as $c): ?>
                <option value="<?php echo $c; ?>" <?php if ($user['country'] == $c) echo 'selected'; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
        </select>

        <label class="form-label">Currency</label>
        <select name="currency" class="form-select" required>
            <?php foreach ($currencies as $cur): ?>
                <option value="<?php echo $cur; ?>" <?php if ($user['currency'] == $cur) echo 'selected'; ?>><?php echo $cur; ?></option>
            <?php endforeach; ?>
        </select>

        <label class="form-label">Employment Status</label>
        <select name="employment" class="form-select" required>
            <option value="employed" <?php if($user['employment']=='employed') echo 'selected'; ?>>Employed</option>
            <option value="self-employed" <?php if($user['employment']=='self-employed') echo 'selected'; ?>>Self-Employed</option>
            <option value="unemployed" <?php if($user['employment']=='unemployed') echo 'selected'; ?>>Unemployed</option>
            <option value="student" <?php if($user['employment']=='student') echo 'selected'; ?>>Student</option>
        </select>

        <button type="submit" class="save-button" name="update_profile">💾 Save Changes</button>
    </form>
</div>
</body>
</html>
