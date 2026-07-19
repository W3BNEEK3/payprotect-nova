/**
 * NovaTrust Toasts
 * Source: novatrust-design-system.md Section 4
 *
 * Usage: NovaToast.success('Card blocked'); NovaToast.error('...');
 *
 * Voice rule: the message should match the action verb exactly — a "Block
 * Card" confirm produces NovaToast.success('Card blocked'), never
 * NovaToast.success('Success!').
 */
(function () {
  'use strict';

  const VARIANTS = {
    success: { icon: 'check_circle', duration: 4000 },
    info: { icon: 'info', duration: 4000 },
    warning: { icon: 'warning', duration: 6000 },
    error: { icon: 'error', duration: null }, // manual dismiss only
  };

  const MAX_VISIBLE = 3;
  let container = null;
  let toastQueue = [];

  function ensureContainer() {
    if (container) {
      return container;
    }
    container = document.createElement('div');
    container.className = 'toast-container';
    container.setAttribute('aria-live', 'polite');
    document.body.appendChild(container);
    return container;
  }

  function dismiss(toastEl) {
    if (!toastEl || toastEl.dataset.dismissing === 'true') {
      return;
    }
    toastEl.dataset.dismissing = 'true';
    toastEl.classList.add('leaving');
    toastEl.addEventListener(
      'animationend',
      () => {
        toastEl.remove();
        toastQueue = toastQueue.filter((t) => t !== toastEl);
      },
      { once: true }
    );
  }

  function show(variant, message) {
    const config = VARIANTS[variant] || VARIANTS.info;
    const containerEl = ensureContainer();

    const toast = document.createElement('div');
    toast.className = `toast toast-${variant}`;
    toast.setAttribute('role', variant === 'error' ? 'alert' : 'status');

    const icon = document.createElement('span');
    icon.className = 'material-symbols-outlined toast-icon';
    icon.textContent = config.icon;

    const msg = document.createElement('div');
    msg.className = 'toast-message';
    msg.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'toast-close';
    closeBtn.setAttribute('aria-label', 'Dismiss');
    closeBtn.innerHTML = '<span class="material-symbols-outlined">close</span>';
    closeBtn.onclick = () => dismiss(toast);

    toast.appendChild(icon);
    toast.appendChild(msg);
    toast.appendChild(closeBtn);

    if (config.duration !== null) {
      const progress = document.createElement('div');
      progress.className = 'toast-progress';
      progress.style.animationDuration = config.duration + 'ms';
      toast.appendChild(progress);

      let remaining = config.duration;
      let startTime = Date.now();
      let timerId = setTimeout(() => dismiss(toast), remaining);

      toast.addEventListener('mouseenter', () => {
        clearTimeout(timerId);
        remaining -= Date.now() - startTime;
        toast.dataset.paused = 'true';
      });

      toast.addEventListener('mouseleave', () => {
        startTime = Date.now();
        timerId = setTimeout(() => dismiss(toast), remaining);
        toast.dataset.paused = 'false';
      });
    }

    containerEl.insertBefore(toast, containerEl.firstChild);
    toastQueue.unshift(toast);

    while (toastQueue.length > MAX_VISIBLE) {
      const oldest = toastQueue.pop();
      dismiss(oldest);
    }

    return toast;
  }

  window.NovaToast = {
    success: (message) => show('success', message),
    info: (message) => show('info', message),
    warning: (message) => show('warning', message),
    error: (message) => show('error', message),
  };
})();
