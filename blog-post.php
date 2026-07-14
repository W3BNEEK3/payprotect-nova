<?php
$posts = [
    1 => [
        'title' => 'How to Use Mobile Banking Safely',
        'author' => 'Admin',
        'date' => 'May 2024',
        'tags' => ['Mobile Banking', 'Security'],
        'image' => 'assets/images/about_image_1.webp',
        'content' => <<<HTML
<p>
Mobile banking is convenient, but safety is essential. Here are the best practices to keep your finances secure:
</p>
<ul>
  <li><b>Download apps only from trusted sources:</b> Use official app stores and avoid third-party downloads.</li>
  <li><b>Enable biometric authentication:</b> Use fingerprint or face ID for an extra layer of security.</li>
  <li><b>Keep your device and app updated:</b> Updates often include important security patches.</li>
  <li><b>Avoid public Wi-Fi:</b> Use your mobile data or a secure network for banking transactions.</li>
  <li><b>Enable transaction notifications:</b> Get instant alerts for every transaction to spot fraud quickly.</li>
  <li><b>Log out after each session:</b> Don’t leave your banking app open in the background.</li>
</ul>
<p>
By following these tips, you can enjoy secure and seamless mobile banking with NovaTrust.
</p>
HTML
    ],
    2 => [
        'title' => 'Setting Up Account Alerts',
        'author' => 'Admin',
        'date' => 'May 2024',
        'tags' => ['How-To', 'Notifications'],
        'image' => 'assets/images/about_image_2.webp',
        'content' => <<<HTML
<p>
Account alerts help you stay informed about your finances and prevent fraud. Here’s how to set them up:
</p>
<ol>
  <li>Log in to your NovaTrust account.</li>
  <li>Navigate to <b>Settings &gt; Alerts</b>.</li>
  <li>Select the types of alerts you want:
    <ul>
      <li>Low balance alerts</li>
      <li>Large transaction notifications</li>
      <li>Login and password change alerts</li>
      <li>Unusual activity alerts</li>
    </ul>
  </li>
  <li>Choose your preferred delivery method (SMS, email, or push notification).</li>
  <li>Save your settings.</li>
</ol>
<p>
Customizing your alerts ensures you’re always in control of your account activity and can respond quickly to any suspicious events.
</p>
HTML
    ],
    3 => [
        'title' => 'Budgeting and Saving Tips for Online Banking',
        'author' => 'Admin',
        'date' => 'April 2024',
        'tags' => ['Financial Tips', 'Budgeting'],
        'image' => 'assets/images/about_image_3.webp',
        'content' => <<<HTML
<p>
Online banking makes budgeting and saving easier than ever. Here’s how to make the most of NovaTrust’s tools:
</p>
<ul>
  <li><b>Track spending by category:</b> Use the analytics dashboard to see where your money goes.</li>
  <li><b>Set and monitor savings goals:</b> Create goals for emergencies, travel, or big purchases and track your progress.</li>
  <li><b>Automate your savings:</b> Set up recurring transfers to your savings account.</li>
  <li><b>Review statements regularly:</b> Spot trends and adjust your budget as needed.</li>
</ul>
<p>
Smart budgeting helps you achieve your financial goals faster and reduces stress.
</p>
HTML
    ],
    4 => [
        'title' => 'What to Do If You Suspect Fraud',
        'author' => 'Admin',
        'date' => 'April 2024',
        'tags' => ['Security', 'Fraud'],
        'image' => 'assets/images/transactions_card_image.png',
        'content' => <<<HTML
<p>
If you notice suspicious activity on your account, act quickly:
</p>
<ul>
  <li><b>Contact NovaTrust support immediately:</b> Use the in-app chat or call our hotline.</li>
  <li><b>Change your passwords:</b> Update your login credentials for all banking and email accounts.</li>
  <li><b>Enable two-factor authentication:</b> Add an extra layer of security to your account.</li>
  <li><b>Monitor your account closely:</b> Check for unauthorized transactions and report them.</li>
  <li><b>File a police report if necessary:</b> For serious cases, inform local authorities.</li>
</ul>
<p>
Prompt action can help prevent further loss and secure your finances. NovaTrust is here to support you 24/7.
</p>
HTML
    ],
    5 => [
        'title' => 'New Features in NovaTrust Online Banking',
        'author' => 'Admin',
        'date' => 'March 2024',
        'tags' => ['Product Updates'],
        'image' => 'assets/images/card.png',
        'content' => <<<HTML
<p>
We’re excited to introduce new features to NovaTrust Online Banking!
</p>
<ul>
  <li><b>Enhanced security protocols:</b> Enjoy peace of mind with our latest security upgrades.</li>
  <li><b>Redesigned mobile experience:</b> A fresh look and smoother navigation on all devices.</li>
  <li><b>Faster transfers and payments:</b> Move your money instantly, anytime.</li>
  <li><b>Customizable dashboard widgets:</b> Personalize your banking experience with drag-and-drop widgets.</li>
</ul>
<p>
Update your app today to explore all the new features and enjoy smarter banking.
</p>
HTML
    ],
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = $posts[$id] ?? null;

// Simple reactions (not persistent, just for UI demo)
$reactions = [
    'like' => '👍',
    'love' => '❤️',
    'insightful' => '💡',
    'wow' => '😮'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $post ? htmlspecialchars($post['title']) : 'Blog Post Not Found' ?> | NovaTrust Blog</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <style>
        .nt-blog-article { background:#fff; border-radius:12px; box-shadow:0 2px 16px rgba(0,0,0,0.06); padding:32px 24px; margin-bottom:32px; }
        .nt-blog-meta { color:#607d8b; font-size:0.98rem; margin-bottom:12px; }
        .nt-blog-tags { margin-bottom:18px; }
        .nt-blog-tag { display:inline-block; background:#e3f2fd; color:#1976d2; border-radius:4px; padding:2px 10px; font-size:0.85rem; margin-right:6px; }
        .nt-blog-img { width:50%; max-width:220px; min-width:90px; display:block; margin:0 auto 22px auto; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.07);}
        .nt-reactions { display:flex; gap:18px; margin:24px 0 0 0; font-size:1.3rem; }
        .nt-reaction-btn { background:none; border:none; cursor:pointer; font-size:1.3rem; transition:transform 0.1s; }
        .nt-reaction-btn:hover { transform:scale(1.2);}
        @media (max-width:600px) {
            .nt-blog-article { padding:18px 4vw; }
            .nt-blog-img { width:90%; max-width:180px; }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width:800px; margin:auto; padding:40px 0;">
        <?php if ($post): ?>
            <article class="nt-blog-article">
                <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="nt-blog-img">
                <div class="nt-blog-meta">
                    By <?= htmlspecialchars($post['author']) ?> &bull; <?= htmlspecialchars($post['date']) ?>
                </div>
                <div class="nt-blog-tags">
                    <?php foreach ($post['tags'] as $tag): ?>
                        <span class="nt-blog-tag"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <h1 style="margin-bottom:18px;"><?= htmlspecialchars($post['title']) ?></h1>
                <div style="margin-bottom:18px;"><?= $post['content'] ?></div>
                <div class="nt-reactions">
                    <span style="font-weight:600; color:#607d8b;">React:</span>
                    <?php foreach ($reactions as $key => $icon): ?>
                        <button class="nt-reaction-btn" title="<?= ucfirst($key) ?>"><?= $icon ?></button>
                    <?php endforeach; ?>
                </div>
            </article>
            <a href="blog.php" style="color:var(--accent); text-decoration:underline;">&larr; Back to Blog</a>
        <?php else: ?>
            <h1>Blog Post Not Found</h1>
            <a href="blog.php" style="color:var(--accent); text-decoration:underline;">&larr; Back to Blog</a>
        <?php endif; ?>
    </div>
</body>
</html>