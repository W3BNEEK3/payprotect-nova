/**
 * NovaTrust Public Header
 * Source: novatrust-design-system.md Section 5.1
 */
(function () {
  'use strict';

  const SCROLL_THRESHOLD = 80;

  function initScrollTransition() {
    const header = document.querySelector('.site-header');
    if (!header) {
      return;
    }

    function update() {
      if (window.scrollY > SCROLL_THRESHOLD) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }

    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  function initMobileDrawer() {
    const btn = document.querySelector('.mobile-menu-btn');
    const drawer = document.querySelector('.mobile-drawer');
    const backdrop = document.querySelector('.mobile-drawer-backdrop');
    const closeBtn = document.querySelector('.mobile-drawer-close');

    if (!btn || !drawer || !backdrop) {
      return;
    }

    let lastFocused = null;

    function open() {
      lastFocused = document.activeElement;
      btn.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      drawer.classList.add('open');
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', onKeydown);

      const firstLink = drawer.querySelector('a, button');
      if (firstLink) {
        requestAnimationFrame(() => firstLink.focus());
      }
    }

    function close() {
      btn.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
      drawer.classList.remove('open');
      backdrop.classList.remove('open');
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onKeydown);

      if (lastFocused) {
        lastFocused.focus();
      }
    }

    function onKeydown(e) {
      if (e.key === 'Escape') {
        close();
      }
    }

    btn.addEventListener('click', () => {
      if (drawer.classList.contains('open')) {
        close();
      } else {
        open();
      }
    });

    closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    window.addEventListener('resize', () => {
      if (window.innerWidth > 900 && drawer.classList.contains('open')) {
        close();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initScrollTransition();
    initMobileDrawer();
  });
})();
