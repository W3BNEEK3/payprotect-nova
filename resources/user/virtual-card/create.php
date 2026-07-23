<?php
$customCss = ['/assets/css/cards-page.css'];
ob_start();

/** @var array|null $user */
$userName = htmlspecialchars(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));
if ($userName === '') { $userName = 'CARDHOLDER'; }

$cards = [
    [
        'id'    => 'visa_geo',
        'title' => 'Visa Geometric',
        'desc'  => 'Bold, colorful geometric shapes for a modern lifestyle.',
        'theme' => 'theme-visa-geo',
        'brand' => 'visa',
        'geo'   => '<div class="geo-pattern geo-visa"><div class="geo-shape shape-1"></div><div class="geo-shape shape-2"></div><div class="geo-shape shape-3"></div><div class="geo-shape shape-4"></div></div>',
    ],
    [
        'id'    => 'mc_dark',
        'title' => 'Mastercard Stealth',
        'desc'  => 'Deep navy with elegant circular motifs. Exclusive and refined.',
        'theme' => 'theme-mc-dark',
        'brand' => 'mastercard',
        'geo'   => '<div class="geo-pattern geo-mc-dark"><div class="mc-circle-1"></div><div class="mc-circle-2"></div><div class="mc-dot"></div></div>',
    ],
    [
        'id'    => 'mc_light',
        'title' => 'Platinum Reserve',
        'desc'  => 'Minimalist elegance with a light, sophisticated palette.',
        'theme' => 'theme-mc-light',
        'brand' => 'mastercard',
        'geo'   => '<div class="geo-pattern geo-mc-light"><div class="pl-shape-1"></div><div class="pl-dots"></div></div>',
    ],
];
?>
<div class="create-card-page">
    <div class="create-card-header">
        <h1>Choose Your Card Style</h1>
        <p>Select from our beautifully designed <?= htmlspecialchars(\App\Core\Site::name()) ?> virtual cards</p>
    </div>

    <!-- 3D Carousel -->
    <div class="card-carousel-scene" id="carouselScene">
        <div class="card-carousel-track" id="carouselTrack">
            <?php foreach ($cards as $i => $card): ?>
            <div class="carousel-card <?= $card['theme'] ?>"
                 data-index="<?= $i ?>"
                 data-pos="<?= $i - 0 /* will be set by JS */ ?>"
                 data-id="<?= $card['id'] ?>"
                 onclick="selectCard(<?= $i ?>)">

                <?= $card['geo'] ?>

                <div class="v-card-top">
                    <?php if ($card['brand'] === 'visa'): ?>
                        <span class="logo-visa"><i>VISA</i></span>
                    <?php else: ?>
                        <span>
                            <div class="logo-mc">
                                <div class="mc-red"></div>
                                <div class="mc-yellow"></div>
                                <div class="mc-overlap"></div>
                            </div>
                        </span>
                    <?php endif; ?>
                    <span class="material-symbols-outlined contactless-icon">contactless</span>
                </div>

                <div class="v-card-mid">
                    <div class="v-card-number">**** **** **** ****</div>
                </div>

                <div class="v-card-bottom">
                    <div class="v-card-name"><?= $userName ?></div>
                    <div class="v-card-expiry">
                        <small>Exp.</small>
                        <span>--/--</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Navigation dots -->
    <div class="carousel-dots" id="carouselDots">
        <?php foreach ($cards as $i => $card): ?>
        <button class="carousel-dot <?= $i === 0 ? 'active' : '' ?>" onclick="selectCard(<?= $i ?>)" aria-label="Select <?= htmlspecialchars($card['title']) ?>"></button>
        <?php endforeach; ?>
    </div>

    <!-- Card title + description -->
    <div class="carousel-card-info">
        <h3 id="carouselTitle"><?= htmlspecialchars($cards[0]['title']) ?></h3>
        <p id="carouselDesc"><?= htmlspecialchars($cards[0]['desc']) ?></p>
    </div>

    <!-- Submit form -->
    <form action="/virtual-card/request" method="POST" style="display: flex; justify-content: center; gap: var(--space-4);">
        <?= \App\Middlewares\CsrfMiddleware::field() ?>
        <input type="hidden" name="card_style" id="cardStyleInput" value="<?= htmlspecialchars($cards[0]['id']) ?>">
        <a href="/virtual-card" class="btn btn-secondary" style="text-decoration: none;">
            Cancel
        </a>
        <button type="submit" class="btn btn-primary" id="getCardBtn">
            Get Visa Geometric
        </button>
    </form>
</div>

<script>
(function () {
    'use strict';

    const cards = <?= json_encode(array_map(fn($c) => ['id' => $c['id'], 'title' => $c['title'], 'desc' => $c['desc']], $cards)) ?>;
    let current = 0;

    const cardEls  = document.querySelectorAll('.carousel-card');
    const dots     = document.querySelectorAll('.carousel-dot');
    const titleEl  = document.getElementById('carouselTitle');
    const descEl   = document.getElementById('carouselDesc');
    const inputEl  = document.getElementById('cardStyleInput');
    const btnEl    = document.getElementById('getCardBtn');

    function render() {
        cardEls.forEach((el, i) => {
            const pos = i - current;
            el.setAttribute('data-pos', pos);
        });
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
        titleEl.textContent = cards[current].title;
        descEl.textContent  = cards[current].desc;
        inputEl.value       = cards[current].id;
        btnEl.textContent   = 'Get ' + cards[current].title;
    }

    window.selectCard = function (idx) {
        current = ((idx % cards.length) + cards.length) % cards.length;
        render();
    };

    // Swipe support
    let startX = 0;
    const scene = document.getElementById('carouselScene');
    scene.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    scene.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) selectCard(dx < 0 ? current + 1 : current - 1);
    });

    // Mouse drag
    let dragging = false, dragStartX = 0;
    scene.addEventListener('mousedown', e => { dragging = true; dragStartX = e.clientX; });
    scene.addEventListener('mouseup', e => {
        if (!dragging) return;
        dragging = false;
        const dx = e.clientX - dragStartX;
        if (Math.abs(dx) > 40) selectCard(dx < 0 ? current + 1 : current - 1);
    });

    // Initial render
    render();
})();
</script>

<?php
$content = ob_get_clean();
$sidebarBrand = \App\Core\Site::name();
$currentPath = '/virtual-card';
require __DIR__ . '/../../layouts/app.php';
?>
