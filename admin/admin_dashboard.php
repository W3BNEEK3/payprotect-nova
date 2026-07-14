<?php
session_start();
require_once "../database/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"];

// Get statistics
$stats = [];

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Active users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE account_status = 'Active'");
$stmt->execute();
$stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total transactions
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM transactions");
$stmt->execute();
$stats['total_transactions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total transaction amount
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM transactions WHERE type = 'credit'");
$stmt->execute();
$stats['total_amount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Pending KYC requests
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE is_kyc_verified = 0");
$stmt->execute();
$stats['pending_kyc'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Suspended accounts
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE account_status = 'Suspended'");
$stmt->execute();
$stats['suspended_accounts'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent transactions
$stmt = $conn->prepare("SELECT t.*, u.firstname, u.lastname FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 10");
$stmt->execute();
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent user registrations
$stmt = $conn->prepare("SELECT firstname, lastname, email, created_at, account_status FROM users ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly transaction data for chart
$stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count, SUM(amount) as total FROM transactions GROUP BY month ORDER BY MIN(created_at) DESC LIMIT 6");
$stmt->execute();
$chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$chart_data = array_reverse($chart_data);
$chart_labels = array_map(function($row){ return $row['month']; }, $chart_data);
$chart_counts = array_map(function($row){ return (int)$row['count']; }, $chart_data);
$chart_amounts = array_map(function($row){ return (float)$row['total']; }, $chart_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaTrust Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #333;
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-item {
            margin: 0.5rem 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            background: #667eea;
            color: white;
            transform: translateX(5px);
        }

        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .nav-link.logout {
            color: #ef4444;
            margin-top: 2rem;
        }

        .nav-link.logout:hover {
            background: #ef4444;
            color: white;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .welcome-text {
            color: #64748b;
            font-size: 1.1rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.users { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.transactions { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-icon.kyc { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .stat-icon.suspended { background: linear-gradient(135deg, #43e97b, #38f9d7); }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .chart-section, .recent-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        .transaction-item, .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .transaction-item:last-child, .user-item:last-child {
            border-bottom: none;
        }

        .transaction-info, .user-info {
            flex: 1;
        }

        .transaction-name, .user-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .transaction-details, .user-details {
            font-size: 0.9rem;
            color: #64748b;
        }

        .transaction-amount {
            font-weight: 600;
            color: #059669;
        }

        .transaction-amount.debit {
            color: #dc2626;
        }

        .user-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .user-status.active {
            background: #dcfce7;
            color: #059669;
        }

        .user-status.suspended {
            background: #fef2f2;
            color: #dc2626;
        }

        .user-status.pending {
            background: #fef3c7;
            color: #d97706;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        .mobile-toggle {
            display: none;
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
        }

        .chart-section {
            min-height: 320px;
            max-height: 400px;
            overflow-y: auto;
        }
        .recent-section {
            max-height: 400px;
            overflow-y: auto;
        }
        @media (max-width: 1024px) {
            .chart-section, .recent-section { max-height: 300px; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>👤 Admin Panel</h2>
                <p>NovaTrust Management</p>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_send_money.php" class="nav-link">
                        <i class="fas fa-money-bill-wave"></i>
                        Send Money
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_refunds.php" class="nav-link">
                        <i class="fas fa-undo"></i>
                        Refunds
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_codes.php" class="nav-link">
                        <i class="fas fa-key"></i>
                        Verification Codes
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_virtual_cards.php" class="nav-link">
                        <i class="fas fa-credit-card"></i>
                        Virtual Cards
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_kyc_requests.php" class="nav-link">
                        <i class="fas fa-id-card"></i>
                        KYC Approvals
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_suspend.php" class="nav-link">
                        <i class="fas fa-ban"></i>
                        Suspended Accounts
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        Notifications
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_view_users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        View Users
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_all_transactions.php" class="nav-link">
                        <i class="fas fa-list"></i>
                        All Transactions
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_upgraded_users.php" class="nav-link">
                        <i class="fas fa-level-up-alt"></i>
                        Account Upgrades
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_logout.php" class="nav-link logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div>
                    <button class="mobile-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Admin Dashboard</h1>
                    <p class="welcome-text">Welcome back, <?php echo htmlspecialchars($admin_name); ?>! 👋</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon transactions">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_transactions']); ?></div>
                    <div class="stat-label">Total Transactions</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon kyc">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['pending_kyc']); ?></div>
                    <div class="stat-label">Pending KYC</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon suspended">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['suspended_accounts']); ?></div>
                    <div class="stat-label">Suspended Accounts</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Chart Section -->
                <div class="chart-section">
                    <div class="section-title">
                        <i class="fas fa-chart-line"></i>
                        Transaction Trends
                    </div>
                    <canvas id="transactionChart" width="400" height="200"></canvas>
                </div>

                <!-- Recent Activities -->
                <div class="recent-section">
                    <div class="section-title">
                        <i class="fas fa-history"></i>
                        Recent Activities
                    </div>
                    
                    <div class="recent-transactions">
                        <h4 style="margin-bottom: 1rem; color: #64748b;">Latest Transactions</h4>
                        <?php foreach (array_slice($recent_transactions, 0, 5) as $transaction): ?>
                        <div class="transaction-item">
                            <div class="transaction-info">
                                <div class="transaction-name">
                                    <?php echo htmlspecialchars($transaction['firstname'] . ' ' . $transaction['lastname']); ?>
                                </div>
                                <div class="transaction-details">
                                    <?php echo htmlspecialchars($transaction['description'] ?? $transaction['message'] ?? '-'); ?> • 
                                    <?php echo date('M d, H:i', strtotime($transaction['created_at'])); ?>
                                </div>
                            </div>
                            <div class="transaction-amount <?php echo $transaction['type']; ?>">
                                <?php echo $transaction['type'] === 'credit' ? '+' : '-'; ?>$<?php echo number_format($transaction['amount'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="recent-users" style="margin-top: 2rem;">
                        <h4 style="margin-bottom: 1rem; color: #64748b;">New Registrations</h4>
                        <?php foreach ($recent_users as $user): ?>
                        <div class="user-item">
                            <div class="user-info">
                                <div class="user-name">
                                    <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                                </div>
                                <div class="user-details">
                                    <?php echo htmlspecialchars($user['email']); ?> • 
                                    <?php echo date('M d', strtotime($user['created_at'])); ?>
                                </div>
                            </div>
                            <div class="user-status <?php echo strtolower($user['account_status']); ?>">
                                <?php echo $user['account_status']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Transaction Chart
        const ctx = document.getElementById('transactionChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Transaction Count',
                    data: <?php echo json_encode($chart_counts); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
