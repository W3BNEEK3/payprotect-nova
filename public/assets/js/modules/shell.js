/**
 * NovaTrust Authenticated Shell
 * Source: novatrust-design-system.md Section 5.2
 */
(function () {
  'use strict';

  const COLLAPSE_STORAGE_KEY = 'novatrust_sidebar_collapsed';

  function initSidebarCollapse() {
    const sidebar = document.querySelector('.shell-sidebar');
    const toggle = document.querySelector('.shell-sidebar-collapse-toggle');

    if (!sidebar || !toggle) {
      return;
    }

    if (localStorage.getItem(COLLAPSE_STORAGE_KEY) === 'true') {
      sidebar.classList.add('collapsed');
    }

    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      localStorage.setItem(COLLAPSE_STORAGE_KEY, sidebar.classList.contains('collapsed'));
    });
  }

  function initTopbarScrollShadow() {
    const topbar = document.querySelector('.shell-topbar');
    if (!topbar) {
      return;
    }

    function update() {
      if (window.scrollY > 4) {
        topbar.classList.add('scrolled');
      } else {
        topbar.classList.remove('scrolled');
      }
    }

    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  function initAccountDropdown() {
    const account = document.querySelector('.shell-account');
    const trigger = document.querySelector('.shell-account-trigger');

    if (!account || !trigger) {
      return;
    }

    function close() {
      account.classList.remove('open');
      document.removeEventListener('click', onOutsideClick);
      document.removeEventListener('keydown', onEscape);
    }

    function onOutsideClick(e) {
      if (!account.contains(e.target)) {
        close();
      }
    }

    function onEscape(e) {
      if (e.key === 'Escape') {
        close();
        trigger.focus();
      }
    }

    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = account.classList.toggle('open');
      if (isOpen) {
        document.addEventListener('click', onOutsideClick);
        document.addEventListener('keydown', onEscape);
      } else {
        close();
      }
    });
  }

  /**
   * Mobile secondary drawer — same morphing hamburger-to-X mechanism as the
   * marketing site (Section 5.1 / P6.2's header.js), reused here per P8.3,
   * but opening notifications/settings/profile/logout instead of nav links.
   */
  function initMobileDrawer() {
    const btn = document.querySelector('.shell-mobile-menu-btn');
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
  }

  document.addEventListener('DOMContentLoaded', () => {
    initSidebarCollapse();
    initTopbarScrollShadow();
    initAccountDropdown();
    initMobileDrawer();
  });
})();
