/**
 * NovaTrust Scroll Reveal
 * Source: novatrust-design-system.md Section 5.1
 *
 * "triggered once, not on every scroll pass." Enforced by unobserving each
 * element the moment it's revealed.
 */
(function () {
  'use strict';

  function init() {
    const elements = document.querySelectorAll('.scroll-reveal');

    if (elements.length === 0) {
      return;
    }

    if (!('IntersectionObserver' in window)) {
      elements.forEach((el) => el.classList.add('revealed'));
      return;
    }

    const observer = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            obs.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );

    elements.forEach((el) => observer.observe(el));
  }

  document.addEventListener('DOMContentLoaded', init);
})();
