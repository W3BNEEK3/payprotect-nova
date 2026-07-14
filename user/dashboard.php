<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname, lastname, account_number, currency, balance, refunded_balance, account_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent transactions
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For spending chart: get last 6 months' spending (debits)
$spending_stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%b %Y') as month, SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'debit' GROUP BY month ORDER BY MIN(created_at) DESC LIMIT 6");
$spending_stmt->execute([$user_id]);
$spending_data = $spending_stmt->fetchAll(PDO::FETCH_ASSOC);
$spending_data = array_reverse($spending_data); // oldest first
$chart_labels = array_map(function($row){ return $row['month']; }, $spending_data);
$chart_values = array_map(function($row){ return (float)$row['total']; }, $spending_data);

$firstname = $user['firstname'];
$lastname = $user['lastname'];
$account_number = $user['account_number'];
$currency = $user['currency'];
$balance = number_format($user['balance'], 2);
$refunded_balance = number_format($user['refunded_balance'], 2);
$account_status = $user['account_status'];

// Calculate financial health score (simple algorithm)
$total_balance = $user['balance'] + $user['refunded_balance'];
$health_score = min(100, ($total_balance / 1000) * 100); // Out of 100

// --- Budget Tracking (demo: session or default) ---
if (!isset($_SESSION['budget'])) {
    $_SESSION['budget'] = 500; // default monthly budget
}
if (isset($_POST['set_budget'])) {
    $_SESSION['budget'] = max(1, floatval($_POST['budget_amount']));
}
$monthly_budget = $_SESSION['budget'];
// Calculate this month's spending (debits)
$month = date('Y-m');
$budget_stmt = $conn->prepare("SELECT SUM(amount) as spent FROM transactions WHERE user_id = ? AND type = 'debit' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$budget_stmt->execute([$user_id, $month]);
$budget_row = $budget_stmt->fetch(PDO::FETCH_ASSOC);
$spent_this_month = $budget_row['spent'] ? floatval($budget_row['spent']) : 0;
$budget_percent = min(100, ($spent_this_month / $monthly_budget) * 100);

// --- Savings Goals (demo: session or default) ---


// --- Bill Management (demo: session or default) ---
if (!isset($_SESSION['bills'])) {
    $_SESSION['bills'] = [
        ['name' => 'Netflix Subscription', 'amount' => 15.99, 'due_date' => date('Y-m-d', strtotime('+5 days')), 'category' => 'Entertainment'],
        ['name' => 'Electricity Bill', 'amount' => 89.50, 'due_date' => date('Y-m-d', strtotime('+2 days')), 'category' => 'Utilities'],
        ['name' => 'Phone Bill', 'amount' => 45.00, 'due_date' => date('Y-m-d', strtotime('-1 day')), 'category' => 'Communication'],
        ['name' => 'Gym Membership', 'amount' => 29.99, 'due_date' => date('Y-m-d', strtotime('+12 days')), 'category' => 'Health'],
    ];
}
if (isset($_POST['add_bill'])) {
    $bill_name = trim($_POST['bill_name']);
    $bill_amount = max(0.01, floatval($_POST['bill_amount']));
    $bill_due_date = $_POST['bill_due_date'];
    $bill_category = trim($_POST['bill_category']);
    $_SESSION['bills'][] = ['name' => $bill_name, 'amount' => $bill_amount, 'due_date' => $bill_due_date, 'category' => $bill_category];
}
$bills = $_SESSION['bills'];

// --- Financial Insights (demo data) ---
$insights = [
    'tip' => 'You spent 20% less on dining this month compared to last month!',
    'spending_category' => 'Entertainment',
    'category_amount' => 156.75,
    'category_percent' => 35,
    'savings_tip' => 'Consider setting up automatic transfers to your savings goals.',
    'security_tip' => 'Your account is well-protected with recent login activity.'
];

$kyc_verified = false;
$kyc_stmt = $conn->prepare("SELECT is_kyc_verified FROM users WHERE id = ?");
$kyc_stmt->execute([$user_id]);
$kyc_row = $kyc_stmt->fetch(PDO::FETCH_ASSOC);
if ($kyc_row && $kyc_row['is_kyc_verified'] == 1) {
    $kyc_verified = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NovaTrust Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="../assets/images/novatrust-logo.png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            min-height: 100vh;
            margin: 0;
            color: #222;
            transition: background 0.3s, color 0.3s;
        }
        body.dark {
            background: #181a20;
            color: #e0e0e0;
        }
        .header {
            background: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.07);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            transition: background 0.3s, color 0.3s;
        }
        body.dark .header {
            background: #23263a;
            color: #e0e0e0;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .logo img {
            height: 35px;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-avatar {
            width: 40px; height: 40px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .dark-toggle {
            background: #e9ecef;
            border: none;
            border-radius: 50%;
            width: 38px; height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            color: #667eea;
            transition: background 0.2s;
        }
        .dark-toggle:hover {
            background: #667eea;
            color: #fff;
        }
        body.dark .dark-toggle {
            background: #23263a;
            color: #fff;
        }
        .main-container {
            margin-top: 90px;
            padding: 2rem 1rem;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
            background: #fff;
            transition: background 0.3s, color 0.3s;
        }
        body.dark .main-container {
            background: #23263a;
            color: #e0e0e0;
        }
        .welcome-section {
            background: #fff;
            border-radius: 18px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: background 0.3s, color 0.3s;
        }
        body.dark .welcome-section {
            background: #23263a;
            color: #e0e0e0;
        }
        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        body.dark .welcome-title { color: #fff; }
        .welcome-subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        body.dark .welcome-subtitle { color: #b0b0b0; }
        .balance-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .balance-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: transform 0.3s, background 0.3s, color 0.3s;
            border-left: 5px solid #667eea;
        }
        body.dark .balance-card {
            background: #23263a;
            color: #e0e0e0;
        }
        .balance-card:hover {
            transform: translateY(-5px);
        }
        .balance-title {
            font-size: 0.95rem;
            color: #667eea;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .balance-amount {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        body.dark .balance-amount { color: #fff; }
        .balance-change {
            font-size: 0.95rem;
            color: #4CAF50;
        }
        .quick-actions {
            background: #fff;
            border-radius: 18px;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: background 0.3s, color 0.3s;
        }
        body.dark .quick-actions {
            background: #23263a;
            color: #e0e0e0;
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #667eea;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }
        .action-item {
            text-align: center;
            padding: 1rem 0.5rem;
            border-radius: 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #667eea;
            font-size: 1.05rem;
        }
        .action-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.15);
            background: #667eea;
            color: white;
        }
        body.dark .action-item {
            background: #23263a;
            color: #fff;
            border: 1px solid #333;
        }
        .action-item i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        .chart-section {
            background: #fff;
            border-radius: 18px;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: background 0.3s, color 0.3s;
        }
        body.dark .chart-section {
            background: #23263a;
            color: #e0e0e0;
        }
        .search-bar {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .search-bar input {
            flex: 1;
            padding: 0.7rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            outline: none;
        }
        .search-bar input:focus {
            border-color: #667eea;
        }
        .search-bar button {
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.7rem 1.2rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .search-bar button:hover {
            background: #4956b8;
        }
        .health-section {
            background: #fff;
            border-radius: 18px;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: background 0.3s, color 0.3s;
        }
        body.dark .health-section {
            background: #23263a;
            color: #e0e0e0;
        }
        .health-score {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        .health-circle {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: conic-gradient(#667eea <?php echo $health_score * 3.6; ?>deg, #e9ecef 0deg 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .health-circle::before {
            content: '';
            width: 60px; height: 60px;
            background: white;
            border-radius: 50%;
            position: absolute;
        }
        .health-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            z-index: 1;
        }
        body.dark .health-number { color: #fff; }
        .health-text {
            flex: 1;
        }
        .health-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #667eea;
        }
        .health-description {
            color: #666;
            font-size: 0.95rem;
        }
        body.dark .health-description { color: #b0b0b0; }
        .transactions-section {
            background: #fff;
            border-radius: 18px;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: background 0.3s, color 0.3s;
        }
        body.dark .transactions-section {
            background: #23263a;
            color: #e0e0e0;
        }
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .transaction-item:last-child {
            border-bottom: none;
        }
        .transaction-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .transaction-icon {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .transaction-details h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        .transaction-details p {
            font-size: 0.95rem;
            color: #666;
        }
        .transaction-amount {
            font-weight: 600;
            color: #4CAF50;
        }
        .transaction-amount.negative {
            color: #f44336;
        }
        /* Sidebar Navigation */
        .sidebar {
            position: fixed;
            top: 90px;
            left: 0;
            width: 70px;
            height: calc(100vh - 90px);
            background: #fff;
            box-shadow: 2px 0 20px rgba(0,0,0,0.07);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem 0;
            z-index: 999;
            transition: left 0.3s, background 0.3s, color 0.3s;
        }
        body.dark .sidebar {
            background: #23263a;
            color: #e0e0e0;
        }
        .sidebar.collapsed {
            left: -80px;
        }
        .sidebar-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 50px; height: 50px;
            margin-bottom: 1.2rem;
            border-radius: 12px;
            color: #667eea;
            text-decoration: none;
            font-size: 1.3rem;
            transition: background 0.2s, color 0.2s;
            position: relative;
        }
        .sidebar-item.active, .sidebar-item:hover {
            background: #667eea;
            color: #fff;
        }
        .sidebar-item span {
            font-size: 0.7rem;
            margin-top: 0.2rem;
        }
        .sidebar-item[title]:hover:after {
            content: attr(title);
            position: absolute;
            left: 60px;
            top: 50%;
            transform: translateY(-50%);
            background: #667eea;
            color: #fff;
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            font-size: 0.9rem;
            white-space: nowrap;
            z-index: 1001;
        }
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 100px;
            left: 10px;
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px; height: 40px;
            font-size: 1.3rem;
            z-index: 2000;
            cursor: pointer;
        }
        @media (max-width: 900px) {
            .main-container { padding: 0.5rem 0.2rem; }
            .sidebar { width: 55px; }
            .sidebar-item { width: 40px; height: 40px; font-size: 1.1rem; }
        }
        @media (max-width: 600px) {
            .main-container { padding: 0.5rem 0.1rem; }
            .sidebar { left: -80px; transition: left 0.3s, background 0.3s, color 0.3s; }
            .sidebar.show { left: 0; }
            .sidebar-toggle { display: block; }
        }
        .back-to-dashboard {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px; height: 60px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            z-index: 1000;
            font-size: 2rem;
        }
        .back-to-dashboard:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0,0,0,0.4);
        }
        .budget-section, .goals-section {
            background: #fff;
            border-radius: 18px;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: background 0.3s, color 0.3s;
        }
        body.dark .budget-section, body.dark .goals-section {
            background: #23263a;
            color: #e0e0e0;
        }
        .budget-title, .goals-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #667eea;
        }
        .budget-bar {
            width: 100%;
            height: 18px;
            background: #e9ecef;
            border-radius: 10px;
            margin-bottom: 0.7rem;
            overflow: hidden;
        }
        .budget-bar-inner {
            height: 100%;
            background: #667eea;
            border-radius: 10px 0 0 10px;
            transition: width 0.5s;
        }
        .budget-warning {
            color: #f44336;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .budget-set-form {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .budget-set-form input {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            width: 120px;
        }
        .budget-set-form button {
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.2rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .budget-set-form button:hover {
            background: #4956b8;
        }
        .goals-list {
            margin-top: 1rem;
        }
        .goal-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem 1.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(102,126,234,0.04);
        }
        body.dark .goal-card {
            background: #23263a;
            color: #fff;
            border: 1px solid #333;
        }
        .goal-title {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 0.3rem;
        }
        .goal-bar {
            width: 100%;
            height: 12px;
            background: #e9ecef;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }
        .goal-bar-inner {
            height: 100%;
            background: #667eea;
            border-radius: 8px 0 0 8px;
            transition: width 0.5s;
        }
        .goal-progress {
            font-size: 0.95rem;
            color: #333;
        }
        body.dark .goal-progress { color: #fff; }
        .add-goal-form {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .add-goal-form input {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
        }
        .add-goal-form button {
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.2rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .add-goal-form button:hover {
            background: #4956b8;
        }
        .bill-management-section, .financial-insights-section {
            background: #fff;
            border-radius: 18px;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: background 0.3s, color 0.3s;
        }
        body.dark .bill-management-section, body.dark .financial-insights-section {
            background: #23263a;
            color: #e0e0e0;
        }
        .add-bill-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .add-bill-form input, .add-bill-form select {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
        }
        .add-bill-form button {
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.2rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .add-bill-form button:hover {
            background: #4956b8;
        }
        .bills-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }
        .bill-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.2rem;
            border-left: 4px solid #667eea;
            transition: transform 0.2s;
        }
        body.dark .bill-card {
            background: #23263a;
            color: #fff;
            border: 1px solid #333;
        }
        .bill-card:hover {
            transform: translateY(-2px);
        }
        .bill-card.overdue {
            border-left-color: #f44336;
            background: #fff5f5;
        }
        body.dark .bill-card.overdue {
            background: #2d1b1b;
        }
        .bill-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        body.dark .bill-name { color: #fff; }
        .bill-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.3rem;
        }
        .bill-due-date {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        body.dark .bill-due-date { color: #b0b0b0; }
        .bill-status {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            display: inline-block;
        }
        .bill-status.overdue {
            background: #f44336;
            color: #fff;
        }
        .bill-status.due-soon {
            background: #ff9800;
            color: #fff;
        }
        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .insight-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s;
        }
        body.dark .insight-card {
            background: #23263a;
            color: #fff;
            border: 1px solid #333;
        }
        .insight-card:hover {
            transform: translateY(-3px);
        }
        .insight-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .insight-title {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }
        .insight-content {
            color: #666;
            line-height: 1.5;
        }
        body.dark .insight-content { color: #b0b0b0; }
        .security-center-section {
            background: #fff;
            border-radius: 18px;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transition: background 0.3s, color 0.3s;
        }
        body.dark .security-center-section {
            background: #23263a;
            color: #e0e0e0;
        }
        .security-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        .security-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s;
        }
        body.dark .security-item {
            background: #23263a;
            color: #fff;
            border: 1px solid #333;
        }
        .security-item:hover {
            transform: translateY(-2px);
        }
        .security-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
        }
        .security-icon.enabled {
            background: #4CAF50;
        }
        .security-icon.warning {
            background: #ff9800;
        }
        .security-info {
            flex: 1;
        }
        .security-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.2rem;
        }
        body.dark .security-title { color: #fff; }
        .security-status {
            font-size: 0.9rem;
            color: #666;
        }
        body.dark .security-status { color: #b0b0b0; }
        .security-action {
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .security-action:hover {
            background: #4956b8;
        }
        @media (max-width: 768px) {
            .add-bill-form {
                grid-template-columns: 1fr;
            }
            .bills-list {
                grid-template-columns: 1fr;
            }
            .insights-grid {
                grid-template-columns: 1fr;
            }
            .security-grid {
                grid-template-columns: 1fr;
            }
            .security-item {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<!-- Header -->
<header class="header">
    <div class="logo"><img src="../assets/images/novatrust-logo.png" alt="NovaTrust Bank Logo" style="height:48px; max-width:150px; width:auto;"></div>
    <div class="user-menu">
        <button class="dark-toggle" id="darkToggle" title="Toggle dark mode"><i class="fas fa-moon"></i></button>
        <div class="user-avatar">
            <?php echo strtoupper(substr($firstname, 0, 1)); ?>
        </div>
    </div>
</header>
<!-- Sidebar Navigation -->
<button class="sidebar-toggle" id="sidebarToggle" title="Menu" type="button"><i class="fas fa-bars"></i></button>
<nav class="sidebar" id="sidebarNav">
    <a href="dashboard.php" class="sidebar-item active" title="Dashboard"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="transactions.php" class="sidebar-item" title="Transactions"><i class="fas fa-exchange-alt"></i><span>Transactions</span></a>
    <a href="virtual_card.php" class="sidebar-item" title="Virtual Card"><i class="fas fa-credit-card"></i><span>Card</span></a>
    <a href="refund_balance.php" class="sidebar-item" title="Refund Balance"><i class="fas fa-undo"></i><span>Refund</span></a>
    <a href="notifications.php" class="sidebar-item" title="Notifications"><i class="fas fa-bell"></i><span>Alerts</span></a>
    <a href="settings.php" class="sidebar-item" title="Settings"><i class="fas fa-cog"></i><span>Settings</span></a>
    <a href="support.php" class="sidebar-item" title="Support"><i class="fas fa-headset"></i><span>Support</span></a>
    <a href="customer_service.php" class="sidebar-item" title="Customer Service"><i class="fas fa-comments"></i><span>Help</span></a>
    <?php if (!$kyc_verified): ?>
    <a href="verify_kyc.php" class="sidebar-item" title="KYC Verification" style="color:#d97706;"><i class="fas fa-id-card"></i><span>KYC Verification</span></a>
    <?php endif; ?>
    <a href="logout.php" class="sidebar-item" title="Logout" style="color: #f44336;"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
</nav>
<div class="main-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-title">Welcome back, <?php echo htmlspecialchars($firstname); ?>! 👋</div>
        <div class="welcome-subtitle">Account: <?php echo htmlspecialchars($account_number); ?> | Status: <span style="color: #4CAF50; font-weight: 600;"> <?php echo ucfirst($account_status); ?></span></div>
    </div>
    <!-- Balance Cards -->
    <div class="balance-section">
        <div class="balance-card">
            <div class="balance-title">Available Balance</div>
            <div class="balance-amount"><?php echo $currency . $balance; ?></div>
            <div class="balance-change">+2.5% from last month</div>
        </div>
        <div class="balance-card">
            <div class="balance-title">Refunded Balance</div>
            <div class="balance-amount"><?php echo $currency . $refunded_balance; ?></div>
            <div class="balance-change">Ready for withdrawal</div>
        </div>
        <div class="balance-card">
            <div class="balance-title">Total Balance</div>
            <div class="balance-amount"><?php echo $currency . number_format($user['balance'] + $user['refunded_balance'], 2); ?></div>
            <div class="balance-change">All accounts combined</div>
        </div>
    </div>
    <!-- Spending Trends Chart -->
    <div class="chart-section">
        <div class="section-title">Spending Trends (Last 6 Months)</div>
        <canvas id="spendingChart" height="80"></canvas>
    </div>
    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="section-title">Quick Actions</div>
        <div class="actions-grid">
            <a href="withdraw_bank.php" class="action-item"><i class="fas fa-university"></i><span>Bank Transfer</span></a>
            <a href="withdraw_paypal.php" class="action-item"><i class="fab fa-paypal"></i><span>PayPal</span></a>
            <a href="withdraw_crypto.php" class="action-item"><i class="fab fa-bitcoin"></i><span>Crypto</span></a>
            <a href="withdraw_googlepay.php" class="action-item"><i class="fab fa-google-pay"></i><span>Google Pay</span></a>
            <a href="virtual_card.php" class="action-item"><i class="fas fa-credit-card"></i><span>Virtual Card</span></a>
            <a href="transactions.php" class="action-item"><i class="fas fa-history"></i><span>History</span></a>
            <a href="send_money.php" class="action-item"><i class="fas fa-paper-plane"></i><span>Send Money</span></a>
            <a href="settings.php" class="action-item"><i class="fas fa-cog"></i><span>Settings</span></a>
        </div>
    </div>
    <!-- Financial Health -->
    <div class="health-section">
        <div class="section-title">Financial Health</div>
        <div class="health-score">
            <div class="health-circle">
                <div class="health-number"><?php echo round($health_score); ?></div>
            </div>
            <div class="health-text">
                <div class="health-title">Your Financial Score</div>
                <div class="health-description">
                    <?php 
                    if ($health_score >= 80) echo "Excellent! Your finances are in great shape.";
                    elseif ($health_score >= 60) echo "Good! You're on the right track.";
                    elseif ($health_score >= 40) echo "Fair. Consider adding more funds.";
                    else echo "Needs attention. Consider adding funds to improve your score.";
                    ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Budget Tracking -->
    <div class="budget-section">
        <div class="budget-title">Monthly Budget</div>
        <div class="budget-bar">
            <div class="budget-bar-inner" style="width: <?php echo $budget_percent; ?>%;"></div>
        </div>
        <div>
            <strong><?php echo $currency . number_format($spent_this_month, 2); ?></strong> spent of <strong><?php echo $currency . number_format($monthly_budget, 2); ?></strong>
        </div>
        <?php if ($spent_this_month > $monthly_budget): ?>
            <div class="budget-warning">You have exceeded your budget!</div>
        <?php endif; ?>
        <form method="post" class="budget-set-form">
            <input type="number" name="budget_amount" min="1" step="0.01" value="<?php echo $monthly_budget; ?>" required>
            <button type="submit" name="set_budget">Set Budget</button>
        </form>
    </div>
    <!-- Savings Goals -->
    
    <!-- Bill Management -->
    <div class="bill-management-section">
        <div class="section-title">Bill Management</div>
        <form method="post" class="add-bill-form">
            <input type="text" name="bill_name" placeholder="Bill name (e.g., Netflix, Electricity)" required>
            <input type="number" name="bill_amount" min="0.01" step="0.01" placeholder="Amount" required>
            <input type="date" name="bill_due_date" required>
            <select name="bill_category" required>
                <option value="">Select category</option>
                <option value="Utilities">Utilities</option>
                <option value="Communication">Communication</option>
                <option value="Entertainment">Entertainment</option>
                <option value="Health">Health</option>
                <option value="Transport">Transport</option>
                <option value="Food">Food</option>
                <option value="Other">Other</option>
            </select>
            <button type="submit" name="add_bill">Add Bill</button>
        </form>
        <div class="bills-list">
            <?php foreach ($bills as $bill): 
                $bill_overdue = strtotime($bill['due_date']) < strtotime('today');
                $bill_days_left = ceil((strtotime($bill['due_date']) - strtotime('today')) / (60 * 60 * 24));
            ?>
            <div class="bill-card <?php echo $bill_overdue ? 'overdue' : ''; ?>">
                <div class="bill-name"><?php echo htmlspecialchars($bill['name']); ?></div>
                <div class="bill-amount"><?php echo $currency . number_format($bill['amount'], 2); ?></div>
                <div class="bill-due-date">Due: <?php echo date('M d, Y', strtotime($bill['due_date'])); ?></div>
                <?php if ($bill_overdue): ?>
                    <div class="bill-status overdue">Overdue</div>
                <?php else: ?>
                    <div class="bill-status due-soon">Due in <?php echo $bill_days_left; ?> days</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Financial Insights -->
    <div class="financial-insights-section">
        <div class="section-title">Financial Insights</div>
        <div class="insights-grid">
            <div class="insight-card">
                <div class="insight-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="insight-title">Tip of the Day</div>
                <div class="insight-content"><?php echo htmlspecialchars($insights['tip']); ?></div>
            </div>
            <div class="insight-card">
                <div class="insight-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="insight-title">Spending Analysis</div>
                <div class="insight-content">
                    <p>You spent <strong><?php echo $currency . number_format($insights['category_amount'], 2); ?></strong> on <strong><?php echo htmlspecialchars($insights['spending_category']); ?></strong> this month.</p>
                    <p>This represents <strong><?php echo $insights['category_percent']; ?>%</strong> of your total spending.</p>
                </div>
            </div>
            <div class="insight-card">
                <div class="insight-icon"><i class="fas fa-piggy-bank"></i></div>
                <div class="insight-title">Savings Tip</div>
                <div class="insight-content"><?php echo htmlspecialchars($insights['savings_tip']); ?></div>
            </div>
            <div class="insight-card">
                <div class="insight-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="insight-title">Security Status</div>
                <div class="insight-content"><?php echo htmlspecialchars($insights['security_tip']); ?></div>
            </div>
        </div>
    </div>
    <!-- Security Center -->
    <div class="security-center-section">
        <div class="section-title">Security Center</div>
        <div class="security-grid">
            <div class="security-item">
                <div class="security-icon enabled"><i class="fas fa-lock"></i></div>
                <div class="security-info">
                    <div class="security-title">Two-Factor Authentication</div>
                    <div class="security-status">Enabled</div>
                </div>
                <button class="security-action">Manage</button>
            </div>
            <div class="security-item">
                <div class="security-icon enabled"><i class="fas fa-mobile-alt"></i></div>
                <div class="security-info">
                    <div class="security-title">SMS Notifications</div>
                    <div class="security-status">Active</div>
                </div>
                <button class="security-action">Settings</button>
            </div>
            <div class="security-item">
                <div class="security-icon enabled"><i class="fas fa-clock"></i></div>
                <div class="security-info">
                    <div class="security-title">Last Login</div>
                    <div class="security-status">Today, 2:30 PM</div>
                </div>
                <button class="security-action">View History</button>
            </div>
            <div class="security-item">
                <div class="security-icon warning"><i class="fas fa-key"></i></div>
                <div class="security-info">
                    <div class="security-title">Password</div>
                    <div class="security-status">Last changed 45 days ago</div>
                </div>
                <button class="security-action">Change</button>
            </div>
        </div>
    </div>
    <!-- Recent Transactions -->
    <div class="transactions-section">
        <div class="section-title">Recent Transactions</div>
        <form class="search-bar" id="searchForm" onsubmit="return false;">
            <input type="text" id="searchInput" placeholder="Search transactions...">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
        <div id="transactionsList">
        <?php if (empty($recent_transactions)): ?>
            <p style="text-align: center; color: #666; padding: 2rem;">No recent transactions</p>
        <?php else: ?>
            <?php foreach ($recent_transactions as $transaction): ?>
                <div class="transaction-item">
                    <div class="transaction-info">
                        <div class="transaction-icon">
                            <i class="fas fa-<?php echo $transaction['type'] === 'credit' ? 'arrow-down' : 'arrow-up'; ?>"></i>
                        </div>
                        <div class="transaction-details">
                            <h4><?php echo htmlspecialchars($transaction['message']); ?></h4>
                            <p><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="transaction-amount <?php echo $transaction['type'] === 'debit' ? 'negative' : ''; ?>">
                        <?php echo ($transaction['type'] === 'credit' ? '+' : '-') . $currency . number_format($transaction['amount'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="transactions.php" style="color: #667eea; text-decoration: none; font-weight: 600;">View All Transactions →</a>
        </div>
    </div>
</div>
<!-- Back to Dashboard Button (for subpages) -->

<script>
// Chart.js spending chart
const ctx = document.getElementById('spendingChart').getContext('2d');
const spendingChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Spending',
            data: <?php echo json_encode($chart_values); ?>,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102,126,234,0.08)',
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#667eea',
            fill: true,
        }]
    },
    options: {
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
// Sidebar toggle for mobile
const sidebar = document.getElementById('sidebarNav');
const sidebarToggle = document.getElementById('sidebarToggle');
function handleSidebarToggle(event) {
    if (window.innerWidth <= 600) {
        event.stopPropagation();
        sidebar.classList.toggle('show');
    }
}
sidebarToggle.addEventListener('click', handleSidebarToggle);
// Prevent sidebar from closing when clicking inside
sidebar.addEventListener('click', function(event) {
    event.stopPropagation();
});
// Hide sidebar when clicking outside (mobile only)
document.addEventListener('click', function(event) {
    if (window.innerWidth > 600) return;
    if (!sidebar.contains(event.target) && event.target !== sidebarToggle) {
        sidebar.classList.remove('show');
    }
});
// Dark mode toggle
const darkToggle = document.getElementById('darkToggle');
darkToggle.addEventListener('click', function() {
    document.body.classList.toggle('dark');
});
// Transaction search
const searchForm = document.getElementById('searchForm');
const searchInput = document.getElementById('searchInput');
const transactionsList = document.getElementById('transactionsList');
const allTransactions = Array.from(transactionsList.children);
searchForm.addEventListener('submit', function() {
    const query = searchInput.value.toLowerCase();
    allTransactions.forEach(function(item) {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
    });
});
</script>
<script src="//code.tidio.co/qp9l90ajcrfdgztr6oydtvtt6qldij7e.js" async></script>
</body>
</html>