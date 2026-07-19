<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | NovaTrust</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="icon" type="image/png" href="assets/images/novatrust-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .blog-hero { background: linear-gradient(90deg, #0a2540 60%, #00bcd4 100%); color: #fff; padding: 48px 0 32px 0; text-align: center; }
        .blog-hero h1 { font-size: 2.7rem; margin-bottom: 10px; }
        .blog-hero p { font-size: 1.2rem; max-width: 600px; margin: 0 auto; }
        .blog-search { margin: 24px auto 0 auto; max-width: 400px; display: flex; }
        .blog-search input { flex: 1; padding: 10px; border-radius: 6px 0 0 6px; border: none; font-size: 1rem; }
        .blog-search button { padding: 10px 18px; border: none; background: var(--accent, #00bcd4); color: #fff; border-radius: 0 6px 6px 0; cursor: pointer; font-weight: 600; }
        .blog-main { display: flex; flex-wrap: wrap; gap: 32px; max-width: 1200px; margin: 40px auto; }
        .blog-content { flex: 3 1 600px; }
        .blog-sidebar { flex: 1 1 280px; background: #f9fafd; border-radius: 12px; padding: 24px; min-width: 260px; }
        .blog-categories { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; }
        .blog-category { background: #e0f7fa; color: #007c91; border: none; border-radius: 20px; padding: 6px 18px; font-size: 1rem; cursor: pointer; transition: background 0.2s; }
        .blog-category:hover { background: #00bcd4; color: #fff; }
        .blog-featured { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); padding: 32px; margin-bottom: 32px; }
        .blog-featured h2 { margin-top: 0; }
        .blog-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .blog-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 24px; display: flex; flex-direction: column; }
        .blog-card img { width: 100%; border-radius: 8px; margin-bottom: 12px; }
        .blog-card .blog-meta { font-size: 0.95rem; color: #888; margin-bottom: 8px; }
        .blog-card h3 { margin: 0 0 10px 0; font-size: 1.2rem; }
        .blog-card p { flex: 1; }
        .blog-card a { color: var(--accent, #00bcd4); text-decoration: underline; font-weight: 600; margin-top: 10px; }
        .newsletter-box { background: #fff; border-radius: 10px; padding: 18px; margin-bottom: 24px; text-align: center; }
        .newsletter-box input { width: 70%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 10px; }
        .newsletter-box button { padding: 8px 18px; border: none; background: var(--accent, #00bcd4); color: #fff; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .sidebar-section { margin-bottom: 32px; }
        .sidebar-section h4 { margin-bottom: 12px; }
        .recent-posts-list { list-style: none; padding: 0; margin: 0; }
        .recent-posts-list li { margin-bottom: 10px; }
        @media (max-width: 900px) { .blog-main { flex-direction: column; } .blog-sidebar { max-width: 100%; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"><img src="assets/images/novatrust-logo.png" alt="NovaTrust Bank Logo" style="height:54px; max-width:160px; width:auto;"></div>
        <div class="menu-toggle" id="menuToggle" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="nav" id="nav">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="blog.php">Blog</a>
            <a href="user/login.php">Login</a>
            <a href="user/register.php" class="cta">Register</a>
        </div>
    </div>
    <section class="blog-hero">
        <h1>Bank Smarter: Tips, News & Security for Your Financial Future</h1>
        <p>Welcome to the NovaTrust Blog – your trusted source for online banking tips, security best practices, product updates, and financial wellness advice.</p>
        <form class="blog-search" action="#" method="get" autocomplete="on">
            <input type="text" name="q" placeholder="Search articles..." autocomplete="search">
            <button type="submit">Search</button>
        </form>
    </section>
    <main class="blog-main">
        <section class="blog-content">
            <div class="blog-categories">
                <button class="blog-category">All</button>
                <button class="blog-category">Security</button>
                <button class="blog-category">Mobile Banking</button>
                <button class="blog-category">How-To</button>
                <button class="blog-category">Financial Tips</button>
                <button class="blog-category">Product Updates</button>
            </div>
            <div class="blog-featured">
                <span class="blog-meta">Security • June 2024 • by Admin</span>
                <h2>Online Banking Security Best Practices</h2>
                <p>Protect your finances with these essential online banking security tips: use strong passwords, enable two-factor authentication, avoid public Wi-Fi, and recognize phishing attempts. Learn how to keep your account safe and what to do if you suspect fraud.</p>
                <a href="#">Read Full Article</a>
            </div>
            <div class="blog-grid">
                <div class="blog-card">
                    <img src="assets/images/about_image_1.webp" alt="Mobile Banking Safety">
                    <span class="blog-meta">Mobile Banking • May 2024 • by Admin</span>
                    <h3>How to Use Mobile Banking Safely</h3>
                    <p>Discover the safest ways to use mobile banking: download apps from trusted sources, set up biometric authentication, and keep your device updated for maximum protection.</p>
                    <a href="blog-post.php?id=1">Read More</a>
                </div>
                <div class="blog-card">
                    <img src="assets/images/about_image_2.webp" alt="Account Alerts">
                    <span class="blog-meta">How-To • May 2024 • by Admin</span>
                    <h3>Setting Up Account Alerts</h3>
                    <p>Stay on top of your finances by enabling account alerts for low balances, large transactions, and logins. This guide walks you through the setup process step by step.</p>
                    <a href="blog-post.php?id=2">Read More</a>
                </div>
                <div class="blog-card">
                    <img src="assets/images/about_image_3.webp" alt="Budgeting Tips">
                    <span class="blog-meta">Financial Tips • April 2024 • by Admin</span>
                    <h3>Budgeting and Saving Tips for Online Banking</h3>
                    <p>Learn how to use online banking tools to track spending, set savings goals, and manage your budget more effectively.</p>
                    <a href="blog-post.php?id=3">Read More</a>
                </div>
                <div class="blog-card">
                    <img src="assets/images/transactions_card_image.png" alt="Fraud Response">
                    <span class="blog-meta">Security • April 2024 • by Admin</span>
                    <h3>What to Do If You Suspect Fraud</h3>
                    <p>Act fast if you notice suspicious activity: contact your bank, change your passwords, and monitor your accounts closely. Here’s what you need to know.</p>
                    <a href="blog-post.php?id=4">Read More</a>
                </div>
                <div class="blog-card">
                    <img src="assets/images/card.png" alt="Product Updates">
                    <span class="blog-meta">Product Updates • March 2024 • by Admin</span>
                    <h3>New Features in NovaTrust Online Banking</h3>
                    <p>Explore the latest updates to our online banking platform, including improved security, new mobile features, and enhanced user experience.</p>
                    <a href="blog-post.php?id=5">Read More</a>
                </div>
            </div>
        </section>
        <aside class="blog-sidebar">
            <div class="newsletter-box">
                <h4>Subscribe to Our Newsletter</h4>
                <form action="#" method="post" autocomplete="on">
                    <input type="email" name="email" placeholder="Your email address" required autocomplete="email"><br>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
            <div class="sidebar-section">
                <h4>Recent Posts</h4>
                <ul class="recent-posts-list">
                    <li><a href="#">Online Banking Security Best Practices</a></li>
                    <li><a href="#">How to Use Mobile Banking Safely</a></li>
                    <li><a href="#">Setting Up Account Alerts</a></li>
                    <li><a href="#">Budgeting and Saving Tips for Online Banking</a></li>
                    <li><a href="#">New Features in NovaTrust Online Banking</a></li>
                </ul>
            </div>
            <div class="sidebar-section">
                <h4>Follow Us</h4>
                <div style="display:flex; gap:12px;">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-x-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </aside>
    </main>
    <?php include 'footer.php'; ?>
    <script>
function toggleMenu() {
    var nav = document.getElementById('nav');
    nav.classList.toggle('open');
}
</script>
</body>
</html>
