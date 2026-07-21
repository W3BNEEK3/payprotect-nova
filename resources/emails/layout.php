<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?= htmlspecialchars($subject ?? 'NovaTrust Notification') ?></title>
    <style>
        body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.5; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
        table { border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; }
        table td { font-family: sans-serif; font-size: 15px; vertical-align: top; }
        .body { background-color: #f8fafc; width: 100%; padding: 20px 0; }
        .container { display: block; margin: 0 auto !important; max-width: 580px; padding: 10px; width: 580px; }
        .content { box-sizing: border-box; display: block; margin: 0 auto; max-width: 580px; padding: 10px; }
        .main { background: #ffffff; border-radius: 12px; width: 100%; border: 1px solid #e2e8f0; overflow: hidden; }
        .wrapper { box-sizing: border-box; padding: 40px; }
        .footer { clear: both; margin-top: 20px; text-align: center; width: 100%; }
        .footer td, .footer p, .footer span, .footer a { color: #64748b; font-size: 13px; text-align: center; }
        h1, h2, h3, h4 { color: #0f172a; font-family: sans-serif; font-weight: 600; line-height: 1.4; margin: 0; margin-bottom: 20px; }
        h1 { font-size: 22px; font-weight: 700; }
        p, ul, ol { font-family: sans-serif; font-size: 15px; font-weight: normal; margin: 0; margin-bottom: 20px; color: #334155; }
        a { color: #0f766e; text-decoration: none; font-weight: 500; }
        a:hover { text-decoration: underline; }
        .btn { box-sizing: border-box; width: 100%; margin-bottom: 20px; }
        .btn > tbody > tr > td { padding-bottom: 15px; }
        .btn table { width: auto; }
        .btn table td { background-color: #ffffff; border-radius: 8px; text-align: center; }
        .btn a { background-color: #ffffff; border: solid 1px #0f766e; border-radius: 8px; box-sizing: border-box; color: #0f766e; cursor: pointer; display: inline-block; font-size: 15px; font-weight: 600; margin: 0; padding: 12px 28px; text-decoration: none; text-transform: capitalize; }
        .btn-primary table td { background-color: #0f766e; }
        .btn-primary a { background-color: #0f766e; border-color: #0f766e; color: #ffffff; }
        .header-brand { text-align: center; margin-bottom: 30px; }
        .header-brand h2 { margin: 0; color: #0f766e; letter-spacing: -0.5px; font-size: 24px; font-weight: 700; }
        .hr { border: 0; border-bottom: 1px solid #e2e8f0; margin: 20px 0; }
        .subtext { font-size: 13px; color: #64748b; margin-top: 30px; }
        @media only screen and (max-width: 620px) {
            table.body h1 { font-size: 24px !important; margin-bottom: 10px !important; }
            table.body p, table.body ul, table.body ol, table.body td, table.body span, table.body a { font-size: 15px !important; }
            table.body .wrapper, table.body .article { padding: 24px !important; }
            table.body .content { padding: 0 !important; }
            table.body .container { padding: 0 !important; width: 100% !important; }
            table.body .main { border-radius: 0 !important; border-left: none !important; border-right: none !important; }
            table.body .btn table { width: 100% !important; }
            table.body .btn a { width: 100% !important; }
        }
    </style>
</head>
<body class="">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td>&nbsp;</td>
            <td class="container">
                <div class="content">
                    <table role="presentation" class="main">
                        <tr>
                            <td class="wrapper">
                                <div class="header-brand">
                                    <h2>NovaTrust</h2>
                                </div>
                                <?= $content ?>
                            </td>
                        </tr>
                    </table>
                    <div class="footer">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <span class="apple-link">NovaTrust Financial Services. All rights reserved.</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>
