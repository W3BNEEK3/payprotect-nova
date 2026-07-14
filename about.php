<?php
// about.php - NovaTrust About Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | NovaTrust</title>
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
    <!-- Hero Section -->
    <section style="padding:60px 0 40px 0; text-align:center; background:var(--primary-light, #f5f8ff);">
        <h1 style="color:var(--primary); font-size:2.7rem; margin-bottom:16px;">Banking Just Got Personal.<br>Welcome to NovaTrust, Your Partner in Financial Freedom.</h1>
        <p style="max-width:700px; margin:0 auto; color:var(--text); font-size:1.25rem;">Empowering your financial journey with secure, reliable, and innovative online banking solutions. Join the thousands of customers who trust us with their financial futures.</p>
    </section>
    <!-- About NovaTrust Section -->
    <section style="padding:40px 0; text-align:center;">
        <h2 style="color:var(--primary); font-size:2rem;">About NovaTrust</h2>
        <p style="max-width:700px; margin:24px auto; color:var(--text); font-size:1.1rem;">Established in 2018, NovaTrust has been dedicated to providing secure, reliable, and innovative financial services to our customers. With a strong foundation built on trust, reliability, and customer-centricity, we have grown to become a leading online bank, serving thousands of customers worldwide.</p>
    </section>
    <!-- Our Mission Section -->
    <section style="padding:40px 0; text-align:center; background:#f9fafd;">
        <h2 style="color:var(--primary); font-size:2rem;">Our Mission</h2>
        <p style="max-width:700px; margin:24px auto; color:var(--text); font-size:1.1rem;">Our mission is to empower individuals and businesses to manage their finances efficiently and effectively. We strive to deliver exceptional customer experiences through our user-friendly online platform, competitive interest rates, and personalized support.</p>
    </section>
    <!-- Our Values Section -->
    <section style="padding:40px 0; text-align:center;">
        <h2 style="color:var(--primary); font-size:2rem;">Our Values</h2>
        <p style="max-width:700px; margin:24px auto 12px auto; color:var(--text); font-size:1.1rem;">At NovaTrust, we uphold the highest standards of:</p>
        <ul style="list-style:none; padding:0; max-width:500px; margin:0 auto; text-align:left; color:var(--text); font-size:1.1rem;">
            <li><b>Integrity:</b> We operate with transparency and honesty in all our dealings.</li>
            <li><b>Security:</b> We prioritize the protection of our customers' assets and information.</li>
            <li><b>Innovation:</b> We continuously innovate and improve our services to meet the evolving needs of our customers.</li>
            <li><b>Customer-centricity:</b> We put our customers at the heart of everything we do.</li>
        </ul>
    </section>
    <!-- Our Team Section -->
    <section style="padding:40px 0; text-align:center; background:#f9fafd;">
        <h2 style="color:var(--primary); font-size:2rem;">Our Team</h2>
        <p style="max-width:700px; margin:24px auto; color:var(--text); font-size:1.1rem;">Our team of experienced professionals is committed to delivering exceptional service and support to our customers. With expertise in finance, technology, and customer service, we work together to ensure that NovaTrust remains a trusted and reliable partner for our customers.</p>
    </section>
    <!-- Join Us Section -->
    <section style="padding:40px 0; text-align:center;">
        <h2 style="color:var(--primary); font-size:2rem;">Join Us</h2>
        <p style="max-width:700px; margin:24px auto; color:var(--text); font-size:1.1rem;">Join the NovaTrust community today and discover a smarter way to bank online. Experience the benefits of our secure, reliable, and innovative financial services, and take control of your finances with confidence.</p>
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