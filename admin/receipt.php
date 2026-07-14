<?php
session_start();
require_once "../database/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['transaction_id'])) {
    echo "<p style='color:red;text-align:center;margin-top:2rem;'>No transaction ID provided.</p>";
    exit();
}
$transaction_id = intval($_GET['transaction_id']);

$stmt = $conn->prepare("SELECT t.*, u.firstname, u.lastname, u.email FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$transaction_id]);
$txn = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$txn) {
    echo "<p style='color:red;text-align:center;margin-top:2rem;'>Transaction not found.</p>";
    exit();
}

$type_label = $txn['type'] === 'credit' ? 'Credit' : 'Debit';
$type_color = $txn['type'] === 'credit' ? '#059669' : '#dc2626';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt - NovaTrust Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            margin: 0;
            color: #222;
        }
        .receipt-container {
            max-width: 480px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 2.5rem 2rem 2rem 2rem;
            position: relative;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .receipt-header h2 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .receipt-header .icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .receipt-details {
            margin-bottom: 2rem;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1.08rem;
        }
        .detail-label {
            color: #64748b;
        }
        .detail-value {
            font-weight: 600;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .amount-label {
            color: #64748b;
            font-size: 1.1rem;
        }
        .amount-value {
            font-size: 2rem;
            font-weight: 700;
            color: <?php echo $type_color; ?>;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 2rem;
        }
        .back-btn {
            display: inline-block;
            background: #667eea;
            color: #fff;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: background 0.2s;
        }
        .back-btn:hover {
            background: #4956b8;
        }
        .download-btn {
            display: inline-block;
            background: #059669;
            color: #fff;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            margin-left: auto;
            margin-right: auto;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }
        .download-btn:hover {
            background: #047857;
        }
        @media (max-width: 600px) {
            .receipt-container {
                padding: 1.2rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container" id="receiptCard">
        <button class="download-btn" id="downloadPDF"><i class="fas fa-download"></i> Download PDF</button>
        <div class="receipt-header">
            <div class="icon">
                <i class="fas fa-receipt"></i>
            </div>
            <h2>Transaction Receipt</h2>
            <div style="color:#64748b; font-size:1rem;">#<?php echo $txn['id']; ?></div>
        </div>
        <div class="amount-row">
            <div class="amount-label">Amount</div>
            <div class="amount-value">
                <?php echo $txn['currency'] . ' ' . number_format($txn['amount'], 2); ?>
            </div>
        </div>
        <div class="receipt-details">
            <div class="detail-row">
                <div class="detail-label">Type</div>
                <div class="detail-value" style="color:<?php echo $type_color; ?>;"><?php echo $type_label; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">User</div>
                <div class="detail-value"><?php echo htmlspecialchars($txn['firstname'] . ' ' . $txn['lastname']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($txn['email']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Date</div>
                <div class="detail-value"><?php echo date('M d, Y H:i', strtotime($txn['created_at'])); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Description</div>
                <div class="detail-value"><?php echo htmlspecialchars($txn['description'] ?? $txn['message'] ?? '-'); ?></div>
            </div>
            <?php if (!empty($txn['status'])): ?>
            <div class="detail-row">
                <div class="detail-label">Status</div>
                <div class="detail-value"><?php echo htmlspecialchars($txn['status']); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($txn['method'])): ?>
            <div class="detail-row">
                <div class="detail-label">Method</div>
                <div class="detail-value"><?php echo htmlspecialchars($txn['method']); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($txn['reference'])): ?>
            <div class="detail-row">
                <div class="detail-label">Reference</div>
                <div class="detail-value"><?php echo htmlspecialchars($txn['reference']); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="receipt-footer">
            <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
    <script>
    document.getElementById('downloadPDF').addEventListener('click', function() {
        const receipt = document.getElementById('receiptCard');
        html2canvas(receipt, { scale: 2 }).then(function(canvas) {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new window.jspdf.jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            // Calculate image dimensions to fit A4
            const imgWidth = pageWidth - 40;
            const imgHeight = canvas.height * imgWidth / canvas.width;
            pdf.addImage(imgData, 'PNG', 20, 20, imgWidth, imgHeight);
            pdf.save('NovaTrust_Receipt_<?php echo $txn['id']; ?>.pdf');
        });
    });
    </script>
</body>
</html> 