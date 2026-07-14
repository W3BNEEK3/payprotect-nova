<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer Withdrawal</title>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 32px 24px;
            text-align: center;
            color: white;
        }

        .header h2 {
            font-family: 'Google Sans', sans-serif;
            font-size: 28px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .form-content {
            padding: 32px 24px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #5f6368;
            margin-bottom: 8px;
            transition: color 0.2s;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-group input:hover,
        .form-group select:hover {
            border-color: #bdbdbd;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Google Sans', sans-serif;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            margin-top: 8px;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .helper-text {
            font-size: 12px;
            color: #5f6368;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .required {
            color: #d93025;
        }

        .info-banner {
            background: #e8f0fe;
            border-left: 4px solid #1a73e8;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #1967d2;
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
            }

            .container {
                border-radius: 0;
                min-height: 100vh;
            }

            .header {
                padding: 24px 20px;
            }

            .header h2 {
                font-size: 24px;
            }

            .form-content {
                padding: 24px 20px;
            }
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Bank Transfer Withdrawal</h2>
            <p>Securely withdraw funds to your bank account</p>
        </div>

        <div class="form-content">
            <div class="info-banner">
                Processing typically takes 2-5 business days. Please ensure all details are accurate.
            </div>

            <form action="process_withdraw.php" method="POST">
                <input type="hidden" name="withdraw_method" value="bank">

                <div class="form-group">
                    <label for="account_holder_name">Account Holder Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" id="account_holder_name" name="account_holder_name" required placeholder="John Doe">
                    </div>
                </div>

                <div class="form-group">
                    <label for="account_number">Account Number / IBAN <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" id="account_number" name="account_number" required placeholder="GB29 NWBK 6016 1331 9268 19">
                    </div>
                </div>

                <div class="form-group">
                    <label for="bank_name">Bank Name <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" id="bank_name" name="bank_name" required placeholder="Chase Bank">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="swift_code">SWIFT/BIC Code <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" id="swift_code" name="swift_code" required placeholder="CHASUS33">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bank_country">Bank Country <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" id="bank_country" name="bank_country" required placeholder="United States">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="currency">Currency <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" id="currency" name="currency" required placeholder="USD">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="amount">Amount <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="number" id="amount" step="0.01" name="amount" required placeholder="1000.00">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason">Reason for Withdrawal <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" id="reason" name="reason" required placeholder="Personal use">
                    </div>
                    <div class="helper-text">Brief description of the purpose</div>
                </div>

                <button type="submit" name="submit_withdrawal">Submit Withdrawal Request</button>
            </form>
        </div>
    </div>
</body>
</html>