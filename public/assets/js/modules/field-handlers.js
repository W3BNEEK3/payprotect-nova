/**
 * NovaTrust Field-Type Handlers
 * Source: novatrust-design-system.md Section 6.2
 */
(function () {
  'use strict';

  const Validators = {
    email(value) {
      if (!value) {
        return 'Enter a valid email address';
      }
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(value) ? null : 'Enter a valid email address';
    },

    phone(value) {
      const digits = value.replace(/\D/g, '');
      if (digits.length < 7 || digits.length > 15) {
        return 'Enter a valid phone number';
      }
      return null;
    },

    amount(value) {
      const numeric = parseFloat(String(value).replace(/,/g, ''));
      if (isNaN(numeric) || numeric <= 0) {
        return 'Enter a valid amount';
      }
      return null;
    },

    cardNumber(value, field, minLength = 13, maxLength = 19) {
      const digits = value.replace(/\D/g, '');
      if (digits.length < minLength || digits.length > maxLength) {
        return 'Enter a valid card number';
      }
      return null;
    },

    cvv(value) {
      return /^\d{3,4}$/.test(value) ? null : 'Enter a valid security code';
    },

    iban(value, field, pattern) {
      const cleaned = value.replace(/\s/g, '').toUpperCase();
      const re = pattern || /^[A-Z0-9]{15,34}$/;
      return re.test(cleaned) ? null : 'Enter a valid account number';
    },

    cryptoAddress(value, field, network) {
      const patterns = {
        btc: /^(bc1|[13])[a-zA-HJ-NP-Z0-9]{25,62}$/,
        eth: /^0x[a-fA-F0-9]{40}$/,
      };
      const re = patterns[network];
      if (!re) {
        return null;
      }
      return re.test(value) ? null : 'Enter a valid wallet address for this network';
    },

    passwordMinimum(value) {
      return value.length >= 8 ? null : 'Password must be at least 8 characters';
    },
  };

  function passwordStrength(value) {
    let score = 0;
    if (value.length >= 8) score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[0-9]/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;

    if (score <= 1) return { level: 'weak', label: 'Weak' };
    if (score <= 3) return { level: 'medium', label: 'Medium' };
    return { level: 'strong', label: 'Strong' };
  }

  const Formatters = {
    phoneGroup(value) {
      const digits = value.replace(/\D/g, '');
      return digits.replace(/(\d{1,3})(?=(\d{3})+(?!\d))/g, '$1 ').trim();
    },

    amountThousands(value) {
      const numeric = parseFloat(String(value).replace(/,/g, ''));
      if (isNaN(numeric)) {
        return value;
      }
      return numeric.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    cardGroups(value) {
      const digits = value.replace(/\D/g, '');
      return digits.replace(/(.{4})/g, '$1 ').trim();
    },

    ibanGroups(value) {
      const cleaned = value.replace(/\s/g, '').toUpperCase();
      return cleaned.replace(/(.{4})/g, '$1 ').trim();
    },

    truncateCryptoAddress(address, headChars = 6, tailChars = 4) {
      if (address.length <= headChars + tailChars) {
        return address;
      }
      return address.slice(0, headChars) + '\u2026' + address.slice(-tailChars);
    },
  };

  function wireAmountLiveWarning(field, getAvailableBalance) {
    const warningId = field.id + '-balance-warning';

    field.addEventListener('input', () => {
      const numeric = parseFloat(field.value.replace(/,/g, ''));
      const available = getAvailableBalance();

      let warningEl = document.getElementById(warningId);

      if (!isNaN(numeric) && numeric > available) {
        if (!warningEl) {
          warningEl = document.createElement('div');
          warningEl.id = warningId;
          warningEl.className = 'input-amount-warning';
          warningEl.innerHTML =
            '<span class="material-symbols-outlined" aria-hidden="true">warning</span>' +
            '<span>Exceeds available balance</span>';
          field.closest('.field-group').appendChild(warningEl);
        }
      } else if (warningEl) {
        warningEl.remove();
      }
    });
  }

  function initOtpGroup(container) {
    const boxes = Array.from(container.querySelectorAll('.otp-box'));

    boxes.forEach((box, index) => {
      box.addEventListener('input', () => {
        box.value = box.value.slice(-1).toUpperCase();
        if (box.value && index < boxes.length - 1) {
          boxes[index + 1].focus();
        }
      });

      box.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !box.value && index > 0) {
          boxes[index - 1].focus();
        }
      });

      box.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').trim().toUpperCase();

        pasted.split('').forEach((char, i) => {
          if (boxes[index + i]) {
            boxes[index + i].value = char;
          }
        });

        const nextEmpty = boxes.find((b) => !b.value) || boxes[boxes.length - 1];
        nextEmpty.focus();
      });
    });
  }

  function initPasswordField(wrapEl) {
    const input = wrapEl.querySelector('input');
    const toggle = wrapEl.querySelector('.password-toggle');
    const strengthFill = wrapEl.parentElement.querySelector('.password-strength-fill');
    const strengthLabel = wrapEl.parentElement.querySelector('.password-strength-label');

    if (toggle) {
      toggle.addEventListener('click', () => {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        toggle.querySelector('.material-symbols-outlined').textContent = isPassword
          ? 'visibility_off'
          : 'visibility';
      });
    }

    if (strengthFill) {
      input.addEventListener('input', () => {
        const { level, label } = passwordStrength(input.value);
        strengthFill.className = 'password-strength-fill ' + level;
        if (strengthLabel) {
          strengthLabel.className = 'password-strength-label ' + level;
          strengthLabel.textContent = label;
        }
      });
    }
  }

  function initCopyButtons(root = document) {
    root.querySelectorAll('.crypto-address .copy-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const fullAddress = btn.closest('.crypto-address').dataset.fullAddress;
        navigator.clipboard.writeText(fullAddress).then(() => {
          if (window.NovaToast) {
            window.NovaToast.success('Address copied');
          }
        });
      });
    });
  }

  window.NovaFieldHandlers = {
    Validators,
    Formatters,
    passwordStrength,
    wireAmountLiveWarning,
    initOtpGroup,
    initPasswordField,
    initCopyButtons,
  };
})();
