<?php
// index.php - NovaTrust Landing Page (Providus Bank style)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaTrust | Future Forward Banking</title>
    <!-- Font Awesome for social icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="icon" type="image/png" href="assets/images/novatrust-logo.png">
</head>
<body>
    <!-- Header -->
  <!-- Header -->
<header class="header">
    <div class="logo">
        <img src="assets/images/novatrust-logo.png" alt="NovaTrust Bank Logo" style="height:54px; max-width:160px; width:auto;">
    </div>
    <nav class="nav" id="nav">
        <a href="#">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="blog.php">Blog</a>
        <a href="user/login.php">Login</a>
        <a href="user/register.php" class="cta">Register</a>
    </nav>
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
    </button>
</header>


    <!-- Hero Section (NovaTrust Adapted) -->
    <section class="nt-hero-section">
        <div class="container">
            <div style="flex:1 1 400px; min-width:320px; z-index:2;">
                <div style="background:rgba(255,255,255,0.07); padding:32px 28px; border-radius:16px;">
                    <div style="font-size:1.1rem; color:var(--accent); font-weight:600; margin-bottom:10px;">All-in-one banking for everyone</div>
                    <h1>The Premier Banking Solution</h1>
                    <p style="font-size:1.2rem; margin-bottom:32px; color:#fff;">Discover online banking with NovaTrust. Experience unmatched security and efficiency for managing your finances.</p>
                    <div style="display:flex; gap:18px; align-items:center;">
                        <a href="user/register.php">Get Started Free Now</a>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="color:#fff; font-size:1.2rem;">4.8</span>
                            <span style="color:gold;">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            </span>
                            <span style="color:#fff;">26 Reviews</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="flex:1 1 400px; min-width:320px; display:flex; justify-content:center; align-items:center;">
                <img src="assets/images/card.png" alt="NovaTrust Card" class="nt-img-hero-card">
            </div>
        </div>
    </section>

    <!-- Features Section (NovaTrust Adapted) -->
    <section class="nt-features-section">
        <div class="container">
            <div style="text-align:center; margin-bottom:40px;">
                <div style="color:var(--primary); font-weight:600;">CORE FEATURES</div>
                <h2>5 Rapid Highlights of Our NovaTrust Solution</h2>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:32px;">
                <div class="card">
                    <img src="assets/icons/icons/bank.png" alt="Early Payments" class="nt-img-feature-icon">
                    <h3 class="card-title">Receive Early Payments Within 24 Hours</h3>
                    <p class="card-desc">Experience the convenience of early payments arriving within just 24 hours. Say goodbye to delays.</p>
                </div>
                <div class="card">
                    <img src="assets/icons/icons/crypto.png" alt="Monitor Expenses" class="nt-img-feature-icon">
                    <h3 class="card-title">Monitor Your Essential Expenses Wisely</h3>
                    <p class="card-desc">Take control of your financial journey by wisely monitoring your essential expenses.</p>
                </div>
                <div class="card">
                    <img src="assets/icons/icons/paypal.png" alt="Send Money" class="nt-img-feature-icon">
                    <h3 class="card-title">Send Money Anywhere, Anytime</h3>
                    <p class="card-desc">Send money to any destination, anytime, and from anywhere with NovaTrust.</p>
                </div>
                <div class="card">
                    <img src="assets/icons/icons/virtual_card_chip_only.png" alt="Virtual or Physical" class="nt-img-feature-icon">
                    <h3 class="card-title">Virtual or Physical - It's Your Choice</h3>
                    <p class="card-desc">Choose the convenience of virtual or physical cards. The choice is yours.</p>
                </div>
                <div class="card">
                    <img src="assets/icons/icons/googlepay.png" alt="B2B & Crypto" class="nt-img-feature-icon">
                    <h3 class="card-title">B2B & Cryptocurrency Payment System</h3>
                    <p class="card-desc">Streamline B2B payments with cryptocurrency integration on NovaTrust.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section (NovaTrust Adapted) -->
    <section class="nt-news-section">
        <div class="container">
            <div style="text-align:center; margin-bottom:40px;">
                <div style="color:var(--primary); font-weight:600;">LATEST NEWS</div>
                <h2>Stay Updated with NovaTrust</h2>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:32px;">
                <div class="card" style="background:var(--secondary);">
                    <h3 class="card-title">NovaTrust Launches New Investment Platform</h3>
                    <p class="card-desc">Our new investment platform offers clients access to global markets with advanced portfolio management tools.</p>
                    <a href="#" style="color:var(--accent); text-decoration:underline;">Read More</a>
                </div>
                <div class="card" style="background:var(--secondary);">
                    <h3 class="card-title">NovaTrust Expands International Services</h3>
                    <p class="card-desc">NovaTrust announces expansion of banking services to key markets, enhancing global connectivity.</p>
                    <a href="#" style="color:var(--accent); text-decoration:underline;">Read More</a>
                </div>
                <div class="card" style="background:var(--secondary);">
                    <h3 class="card-title">New Sustainable Banking Initiative</h3>
                    <p class="card-desc">NovaTrust commits to sustainable banking practices with new green investment options and carbon-neutral operations.</p>
                    <a href="#" style="color:var(--accent); text-decoration:underline;">Read More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section (NovaTrust Adapted) -->
    <section class="nt-about-section" style="background:var(--secondary); padding:60px 0 40px 0;">
        <div class="container" style="max-width:1200px; margin:auto; display:flex; flex-wrap:wrap; align-items:center; gap:40px;">
            <div style="flex:1 1 400px; min-width:320px;">
                <div style="color:var(--primary); font-weight:600;">Who we are</div>
                <h2 style="font-size:2rem; font-weight:700; margin:10px 0 0 0;">Shaping Your Future with Online Banking</h2>
                <p style="margin-bottom:24px;">Embark on a journey towards a brighter financial future with NovaTrust. From managing accounts to convenient bill payments, our platform is designed to empower you.</p>
                <div style="display:flex; gap:24px;">
                    <div style="background:#fff; border-radius:10px; padding:18px 24px; text-align:center;">
                        <div style="color:var(--primary); font-size:1.5rem; font-weight:700;">9M+</div>
                        <div style="color:var(--text);">Daily Transactions</div>
                    </div>
                    <div style="background:#fff; border-radius:10px; padding:18px 24px; text-align:center;">
                        <div style="color:var(--primary); font-size:1.5rem; font-weight:700;">+9%</div>
                        <div style="color:var(--text);">Unlimited Cashback</div>
                    </div>
                </div>
            </div>
            <div style="flex:1 1 400px; min-width:320px; display:flex; gap:16px; flex-wrap:wrap; justify-content:center;">
                <img src="assets/images/about_image_1.webp" alt="About NovaTrust" class="nt-img-about" style="width:180px;">
                <img src="assets/images/about_image_2.webp" alt="About NovaTrust" class="nt-img-about" style="width:120px;">
                <img src="assets/images/about_image_3.webp" alt="About NovaTrust" class="nt-img-about" style="width:120px;">
            </div>
        </div>
    </section>

    <!-- Funfact Section (NovaTrust Adapted) -->
    <section class="nt-funfact-section">
        <div class="container">
            <div style="display:flex; flex-wrap:wrap; gap:32px;">
                <div style="flex:1 1 320px; min-width:260px;">
                    <div class="card" style="background:var(--secondary);">
                        <div style="color:var(--primary); font-size:2rem; font-weight:700;">80%</div>
                        <div style="color:var(--text);">Users primarily access their accounts via mobile for convenient banking on the go.</div>
                    </div>
                    <div class="card" style="background:var(--secondary);">
                        <div style="color:var(--primary); font-size:2rem; font-weight:700;">300K+</div>
                        <div style="color:var(--text);">Gain access to a vast global network of over 300k+ partner ATMs located worldwide.</div>
                    </div>
                </div>
                <div style="flex:1 1 320px; min-width:260px; display:flex; align-items:center; justify-content:center;">
                    <img src="assets/images/transactions_card_image.png" alt="Transactions Card" class="nt-img-funfact">
                </div>
                <div style="flex:1 1 320px; min-width:260px;">
                    <div class="card" style="background:var(--secondary);">
                        <div style="color:var(--primary); font-size:2rem; font-weight:700;">990K+</div>
                        <div style="color:var(--text);">NovaTrust quickly gained popularity among youth, attracting over 990k+ customers in the first year.</div>
                    </div>
                    <div class="card" style="background:var(--secondary);">
                        <div style="color:var(--primary); font-size:2rem; font-weight:700;">60%</div>
                        <div style="color:var(--text);">Users enjoy 60% faster transaction processing times compared to traditional banks.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Section (NovaTrust Adapted) -->
    <section class="nt-money-section">
      <h2>Take control your Money.</h2>
      <div class="nt-money-steps">
        <div class="nt-money-step-card">
          <div class="nt-money-step-icon">🖊️</div>
          <div class="nt-money-step-number">/01</div>
          <div class="nt-money-step-title">Register and Create Your Account</div>
          <div class="nt-money-step-desc">Start your journey by registering for an account and effortlessly creating it.</div>
            </div>
        <div class="nt-money-step-card">
          <div class="nt-money-step-icon">💳</div>
          <div class="nt-money-step-number">/02</div>
          <div class="nt-money-step-title">Effortlessly Manage Your Virtual Cards</div>
          <div class="nt-money-step-desc">Easily manage your virtual cards with our platform. Customize, track, and stay secure.</div>
                    </div>
        <div class="nt-money-step-card">
          <div class="nt-money-step-icon">🏧</div>
          <div class="nt-money-step-number">/03</div>
          <div class="nt-money-step-title">ATM Withdrawals and Online Banking</div>
          <div class="nt-money-step-desc">Easily withdraw cash from ATMs and conveniently manage your banking needs online.</div>
        </div>
        </div>
    </section>

    <!-- Testimonial Section (NovaTrust Adapted) -->
    <section class="nt-testimonial-section">
                <h2>What clients say about us</h2>
      <div class="nt-testimonial-cards">
        <div class="nt-testimonial-card">
          <img src="assets/images/avatar_image_2.webp" class="nt-testimonial-avatar" alt="Reynolds Anthony">
          <div class="nt-testimonial-name">Reynolds Anthony</div>
          <div class="nt-testimonial-rating">★★★★★</div>
          <div class="nt-testimonial-text">"Switching to NovaTrust online banking has been amazing. The seamless interface makes managing finances effortless. Whether tracking, paying bills, or transferring funds, NovaTrust exceeds expectations. Highly recommended for anyone seeking a reliable banking solution."</div>
            </div>
        <div class="nt-testimonial-card">
          <img src="assets/images/avatar_image_4.webp" class="nt-testimonial-avatar" alt="Jane Smith">
          <div class="nt-testimonial-name">Jane Smith</div>
          <div class="nt-testimonial-rating">★★★★½</div>
          <div class="nt-testimonial-text">"NovaTrust has made my banking experience so much easier. The support team is always available and the features are top-notch!"</div>
                            </div>
        <div class="nt-testimonial-card">
          <img src="assets/images/avatar_image_5.webp" class="nt-testimonial-avatar" alt="Michael Johnson">
          <div class="nt-testimonial-name">Michael Johnson</div>
          <div class="nt-testimonial-rating">★★★★★</div>
          <div class="nt-testimonial-text">"I love the flexibility NovaTrust offers. Managing my cards and transactions has never been easier!"</div>
        </div>
        </div>
    </section>

    <!-- Integrations Section (NovaTrust Adapted) -->
    <section class="nt-integrations-section" style="background:var(--secondary); padding:60px 0 40px 0;">
        <div class="container" style="max-width:1200px; margin:auto;">
            <div style="text-align:center; margin-bottom:40px;">
                <div style="color:var(--primary); font-weight:600;">Integrated</div>
                <h2 style="font-size:2rem; font-weight:700; margin:10px 0 0 0;">Mobile wallet integrations</h2>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:32px; justify-content:center; align-items:center;">
                <img src="assets/images/integrated_logo_1.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_2.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_3.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_4.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_5.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_6.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_7.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_8.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_9.webp" alt="Integrated Logo" class="nt-img-integration">
                <img src="assets/images/integrated_logo_10.webp" alt="Integrated Logo" class="nt-img-integration">
            </div>
        </div>
    </section>


    <!-- Trust & Security Certifications -->
    <section class="nt-trustbadges-section" style="background:#fff; padding:32px 0;">
      <div class="container" style="display:flex; flex-wrap:wrap; gap:32px; justify-content:center; align-items:center;">
        <div style="text-align:center;">
          <img src="assets/images/SSL Secure Certification Badge Logo.png" alt="SSL Secure" style="height:56px; max-width:120px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.07); background:#fff; object-fit:contain; display:block; margin:auto;">
          <div style="margin-top:8px; font-size:0.95rem; color:#333;">SSL Secure</div>
        </div>
        <div style="text-align:center;">
          <img src="assets/images/1e8c6eaa-eb87-4336-8ffa-fcb6f7f40b98.jpg" alt="Certified Award" style="height:56px; max-width:120px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.07); background:#fff; object-fit:contain; display:block; margin:auto;">
          <div style="margin-top:8px; font-size:0.95rem; color:#333;">Certified Award</div>
        </div>
        <div style="text-align:center;">
          <img src="assets/images/Certification Badge in Deep Blue and Silver.png" alt="Certification Badge" style="height:56px; max-width:120px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.07); background:#fff; object-fit:contain; display:block; margin:auto;">
          <div style="margin-top:8px; font-size:0.95rem; color:#333;">Certification Badge</div>
        </div>
      </div>
      <div style="text-align:center; margin-top:18px; color:#607d8b; font-size:1.1rem; font-weight:500;">
        Trusted & Certified for Your Security and Peace of Mind
      </div>
    </section>
    <!-- End Trust & Security Certifications -->



    <!-- FAQ Section (NovaTrust) -->
    <section class="nt-faq-section" style="background:var(--secondary); padding:60px 0;">
      <div class="container" style="max-width:800px; margin:auto;">
        <h2 style="text-align:center; margin-bottom:32px;">Frequently Asked Questions</h2>
        <div class="nt-faq-list">
          <div class="nt-faq-item">
            <button class="nt-faq-question">How do I open a NovaTrust account?</button>
            <div class="nt-faq-answer">Click the "Register" button at the top of the page and fill in your details. Your account will be set up in minutes.</div>
          </div>
          <div class="nt-faq-item">
            <button class="nt-faq-question">Is my money safe with NovaTrust?</button>
            <div class="nt-faq-answer">Absolutely. We use advanced encryption and comply with strict banking regulations to keep your funds secure.</div>
          </div>
          <div class="nt-faq-item">
            <button class="nt-faq-question">Are there any hidden fees?</button>
            <div class="nt-faq-answer">No. All fees are clearly displayed in your dashboard. We believe in transparent banking.</div>
          </div>
          <div class="nt-faq-item">
            <button class="nt-faq-question">Can I get both virtual and physical cards?</button>
            <div class="nt-faq-answer">Yes, you can order both virtual and physical cards from your account dashboard at any time.</div>
          </div>
          <div class="nt-faq-item">
            <button class="nt-faq-question">How fast are transfers and withdrawals?</button>
            <div class="nt-faq-answer">Most transfers and withdrawals are processed instantly or within 24 hours, depending on the destination bank.</div>
          </div>
          <div class="nt-faq-item">
            <button class="nt-faq-question">What should I do if I forget my password?</button>
            <div class="nt-faq-answer">Click "Login", then select "Forgot Password" and follow the instructions to reset your password securely.</div>
          </div>
        </div>
      </div>
    </section>
       <script>
  document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menuToggle");
    const nav = document.getElementById("nav");

    menuToggle.addEventListener("click", function () {
      nav.classList.toggle("open");
    });

    // Optional: Close the menu when clicking outside
    document.addEventListener("click", function (e) {
      if (!menuToggle.contains(e.target) && !nav.contains(e.target)) {
        nav.classList.remove("open");
      }
    });
  });
</script>
    <style>
    .nt-faq-list { margin: 0 auto; }
    .nt-faq-item { margin-bottom: 18px; border-radius: 8px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .nt-faq-question {
      width: 100%; text-align: left; background: none; border: none; outline: none;
      font-size: 1.1rem; font-weight: 600; padding: 18px 24px; cursor: pointer; color: var(--primary);
      transition: background 0.2s;
    }
    .nt-faq-question:after {
      content: '\f078';
      font-family: "Font Awesome 6 Free"; font-weight: 900; float: right; transition: transform 0.2s;
    }
    .nt-faq-item.active .nt-faq-question:after { transform: rotate(-180deg); }
    .nt-faq-answer {
      max-height: 0; overflow: hidden; background: #f7f9fa; color: #333; padding: 0 24px;
      font-size: 1rem; transition: max-height 0.3s ease, padding 0.3s;
    }
    .nt-faq-item.active .nt-faq-answer {
      max-height: 200px; padding: 12px 24px 18px 24px;
    }
   
  }
}

    </style>
    <script>
    document.querySelectorAll('.nt-faq-question').forEach(btn => {
      btn.addEventListener('click', function() {
        const item = this.parentElement;
        document.querySelectorAll('.nt-faq-item').forEach(i => {
          if(i !== item) i.classList.remove('active');
        });
        item.classList.toggle('active');
      });
    });
    </script>
    <!-- End FAQ Section -->
    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Notification Popup -->
    <div id="notification"></div>
    <script src="assets/js/menu.js"></script>
    <script>
        (function() {
        const names = [
            "John Doe", "Jane Smith", "Michael Johnson", "Emily Davis", "Chris Brown",
            "Patricia Garcia", "Robert Wilson", "Linda Martinez", "James Anderson", "Maria Taylor",
            "David Thomas", "Sarah Moore", "Richard Clark", "Jessica White", "Daniel Harris",
            "Laura Lewis", "Anthony Walker", "Susan Hall", "Joseph Young", "Karen King"
        ];

        function getRandomAmount() {
            return Math.floor(Math.random() * (100000 - 1000 + 1)) + 1000;
        }

        function getRandomPosition() {
            const top = Math.floor(Math.random() * 80) + 10;
            const left = Math.floor(Math.random() * 80) + 10;
            return { top, left };
        }

        function showNotification(name, amount) {
            const notification = document.getElementById("notification");
            const position = getRandomPosition();

            notification.style.top = `${position.top}vh`;
            notification.style.left = `${position.left}vw`;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="notif-avatar">${name.charAt(0)}</div>
                    <div>
                        <span style="color: #ffffff; font-weight: 500;">${name}</span><br>
                        <span style="color: var(--accent); font-weight: 600;">$${amount.toLocaleString()}</span>
                    </div>
                </div>
            `;
            
            notification.style.display = "block";
            notification.style.animation = "slideIn 0.5s ease-out";

            setTimeout(() => {
                notification.style.animation = "slideOut 0.5s ease-out";
                setTimeout(() => {
                    notification.style.display = "none";
                }, 500);
            }, 5000);
        }

        function simulateWithdrawals() {
            let count = 0;
            const interval = setInterval(() => {
                if (count >= 50) {
                    clearInterval(interval);
                } else {
                    const randomName = names[Math.floor(Math.random() * names.length)];
                    const randomAmount = getRandomAmount();
                    const randomDelay = Math.random() * 1500 + 500;
                    setTimeout(() => showNotification(randomName, randomAmount), randomDelay);
                    count++;
                }
            }, Math.random() * 1500 + 1500);
        }

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                0% {
                    opacity: 0;
                    transform: translateY(20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            @keyframes slideOut {
                0% {
                    opacity: 1;
                    transform: translateY(0);
                }
                100% {
                    opacity: 0;
                    transform: translateY(-20px);
                }
            }
        `;
        document.head.appendChild(style);

        window.onload = simulateWithdrawals;
    })();
    </script>


    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
    // Swiper for Process Section
    var processSwiper = new Swiper('.nt-process-swiper', {
      slidesPerView: 1,
      spaceBetween: 0,
      centeredSlides: true,
      pagination: {
        el: '.nt-process-swiper .swiper-pagination',
        clickable: true,
      },
      breakpoints: {
        700: { slidesPerView: 2, centeredSlides: false, spaceBetween: 24, autoplay: false },
        1024: { slidesPerView: 2, centeredSlides: false, spaceBetween: 24, autoplay: false }
      },
      autoplay: window.innerWidth < 700 ? { delay: 3000, disableOnInteraction: false } : false
    });
    // Swiper for Testimonials Section
    var testimonialsSwiper = new Swiper('.nt-testimonials-swiper', {
      slidesPerView: 1,
      spaceBetween: 24,
      centeredSlides: true,
      pagination: {
        el: '.nt-testimonials-swiper .swiper-pagination',
        clickable: true,
      },
      breakpoints: {
        700: { slidesPerView: 2, centeredSlides: false, autoplay: false },
        1024: { slidesPerView: 3, centeredSlides: false, autoplay: false }
      },
      autoplay: window.innerWidth < 700 ? { delay: 3000, disableOnInteraction: false } : false
    });
    </script>

</body>
</html>
