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
    <title>Withdraw via PayPal</title>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0070ba 0%, #003087 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
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
            background: linear-gradient(135deg, #0070ba 0%, #003087 100%);
            padding: 40px 24px;
            text-align: center;
            color: white;
            position: relative;
        }

        .paypal-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .paypal-icon svg {
            width: 36px;
            height: 36px;
            fill: #0070ba;
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
            padding: 40px 32px;
        }

        .info-banner {
            background: #e7f3ff;
            border-left: 4px solid #0070ba;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 32px;
            font-size: 14px;
            color: #003087;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-icon {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            background: #0070ba;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 28px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #5f6368;
            margin-bottom: 8px;
        }

        .required {
            color: #d93025;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa0a6;
            pointer-events: none;
        }

        .form-group input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .form-group input:focus {
            border-color: #0070ba;
            box-shadow: 0 0 0 4px rgba(0, 112, 186, 0.1);
        }

        .form-group input:hover {
            border-color: #bdbdbd;
        }

        .helper-text {
            font-size: 12px;
            color: #5f6368;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .amount-prefix {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            font-weight: 500;
            color: #5f6368;
            pointer-events: none;
        }

        input[type="number"] {
            padding-left: 32px !important;
        }

        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #0070ba 0%, #003087 100%);
            color: white;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Google Sans', sans-serif;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 112, 186, 0.3);
            margin-top: 8px;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 112, 186, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 112, 186, 0.3);
        }

        .security-note {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #5f6368;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .lock-icon {
            width: 14px;
            height: 14px;
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
                padding: 32px 20px;
            }

            .header h2 {
                font-size: 24px;
            }

            .form-content {
                padding: 32px 20px;
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
            <div class="paypal-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20.067 8.478c.492.88.556 2.014.3 3.327-.74 3.806-3.276 5.12-6.514 5.12h-.5a.805.805 0 0 0-.794.68l-.04.22-.63 3.993-.032.17a.804.804 0 0 1-.794.68H7.72a.483.483 0 0 1-.477-.558L9.213 7.986a.965.965 0 0 1 .952-.81h2.355c3.564 0 6.005 1.086 6.547 3.302z"/>
                    <path d="M9.145 3.102A.965.965 0 0 1 10.096 2.292h5.758c1.917 0 3.452.448 4.435 1.395.906.873 1.342 2.17 1.222 3.905-.417 6.03-3.77 8.15-7.49 8.15h-1.65a.965.965 0 0 0-.952.81L9.448 24H5.997z" opacity=".7"/>
                </svg>
            </div>
            <h2>Withdraw via PayPal</h2>
            <p>Fast and secure withdrawal to your PayPal account</p>
        </div>

        <div class="form-content">
            <div class="info-banner">
                <div class="info-icon">i</div>
                <div>Withdrawals are typically processed within 1-2 business days. Make sure your PayPal email is verified.</div>
            </div>

            <form method="POST" action="process_withdraw.php">
                <input type="hidden" name="withdraw_method" value="paypal">

                <div class="form-group">
                    <label for="paypal_email">PayPal Email Address <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <input type="email" name="paypal_email" id="paypal_email" required placeholder="your.email@example.com">
                    </div>
                    <div class="helper-text">Enter the email linked to your PayPal account</div>
                </div>

                <div class="form-group">
                    <label for="amount">Withdrawal Amount <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <span class="amount-prefix">$</span>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    <div class="helper-text">Minimum withdrawal: $10.00</div>
                </div>

                <button type="submit" name="submit_withdrawal">Submit Withdrawal Request</button>

                <div class="security-note">
                    <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Your information is encrypted and secure
                </div>
            </form>
        </div>
    </div>
</body>
</html>