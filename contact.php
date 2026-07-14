<?php
// contact.php - NovaTrust Contact Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | NovaTrust</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="icon" type="image/png" href="assets/images/novatrust-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <section style="padding:60px 0; text-align:center;">
        <h1 style="color:var(--primary); font-size:2.5rem;">Contact Us</h1>
        <form style="max-width:400px; margin:32px auto; background:#fff; padding:32px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
            <input type="text" name="name" placeholder="Your Name" required style="width:100%; padding:12px; margin-bottom:16px; border-radius:6px; border:1px solid var(--secondary);">
            <input type="email" name="email" placeholder="Your Email" required style="width:100%; padding:12px; margin-bottom:16px; border-radius:6px; border:1px solid var(--secondary);">
            <textarea name="message" placeholder="Your Message" required style="width:100%; padding:12px; margin-bottom:16px; border-radius:6px; border:1px solid var(--secondary);"></textarea>
            <button type="submit" class="btn">Send Message</button>
        </form>
    </section>
    <script>
function toggleMenu() {
    var nav = document.getElementById('nav');
    nav.classList.toggle('open');
}

</script>
<script src="//code.tidio.co/egy2jj6ihpe6ltp2760itz5shteyzupk.js" async></script>
<?php include 'footer.php'; ?>
</body>
</html> 