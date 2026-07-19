/**
 * NovaTrust Confirmation Modal Controller
 * Source: novatrust-design-system.md Section 3
 *
 * Usage:
 *   const confirmed = await NovaModal.confirm({
 *     tier: 'destructive',
 *     icon: 'warning',
 *     title: 'Block this card?',
 *     body: "The cardholder won't be able to use it for any transaction...",
 *     confirmLabel: 'Block Card',
 *   });
 *   if (confirmed) { ... }
 */
(function () {
  'use strict';

  const TIER_CONFIG = {
    primary: { badgeClass: 'tier-primary', btnClass: 'btn-primary' },
    success: { badgeClass: 'tier-success', btnClass: 'btn-success' },
    caution: { badgeClass: 'tier-caution', btnClass: 'btn-caution' },
    destructive: { badgeClass: 'tier-destructive', btnClass: 'btn-destructive' },
  };

  let activeResolve = null;
  let lastFocusedElement = null;

  function getElements() {
    return {
      overlay: document.getElementById('novaModalOverlay'),
      dialog: document.querySelector('#novaModalOverlay .modal-dialog'),
      iconBadge: document.getElementById('novaModalIconBadge'),
      icon: document.getElementById('novaModalIcon'),
      title: document.getElementById('novaModalTitle'),
      body: document.getElementById('novaModalBody'),
      cancelBtn: document.getElementById('novaModalCancel'),
      confirmBtn: document.getElementById('novaModalConfirm'),
      closeBtn: document.getElementById('novaModalClose'),
    };
  }

  function trapFocus(e) {
    const { dialog } = getElements();
    if (!dialog) {
      return;
    }

    if (e.key === 'Escape') {
      close(false);
      return;
    }

    if (e.key !== 'Tab') {
      return;
    }

    const focusable = dialog.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    if (focusable.length === 0) {
      return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  function close(result) {
    const { overlay } = getElements();
    if (!overlay || overlay.hidden) {
      return;
    }

    overlay.classList.add('leaving');

    const onAnimEnd = () => {
      overlay.hidden = true;
      overlay.classList.remove('leaving');
      overlay.removeEventListener('animationend', onAnimEnd);
      document.removeEventListener('keydown', trapFocus);

      if (lastFocusedElement) {
        lastFocusedElement.focus();
        lastFocusedElement = null;
      }

      if (activeResolve) {
        activeResolve(result);
        activeResolve = null;
      }
    };

    overlay.addEventListener('animationend', onAnimEnd, { once: true });
  }

  function confirm(options) {
    const {
      tier = 'primary',
      icon = 'help',
      title,
      body,
      confirmLabel,
      cancelLabel = 'Cancel',
    } = options;

    const els = getElements();

    if (!els.overlay) {
      throw new Error(
        'Confirmation modal markup not found on this page — include ' +
        'components/ui/_confirm-modal.php in the layout.'
      );
    }

    lastFocusedElement = document.activeElement;

    const tierConfig = TIER_CONFIG[tier] || TIER_CONFIG.primary;

    els.iconBadge.className = 'modal-icon-badge ' + tierConfig.badgeClass;
    els.icon.textContent = icon;
    els.title.textContent = title;
    els.body.textContent = body;
    els.cancelBtn.textContent = cancelLabel;
    els.confirmBtn.textContent = confirmLabel;
    els.confirmBtn.className = 'btn ' + tierConfig.btnClass;

    els.overlay.hidden = false;

    els.cancelBtn.onclick = () => close(false);
    els.confirmBtn.onclick = () => close(true);
    els.closeBtn.onclick = () => close(false);
    els.overlay.onclick = (e) => {
      if (e.target === els.overlay) {
        close(false); // backdrop click = Cancel, never Confirm
      }
    };

    document.addEventListener('keydown', trapFocus);

    requestAnimationFrame(() => els.cancelBtn.focus());

    return new Promise((resolve) => {
      activeResolve = resolve;
    });
  }

  window.NovaModal = { confirm };
})();
