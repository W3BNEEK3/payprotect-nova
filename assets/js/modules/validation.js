/**
 * NovaTrust Validation Engine
 * Source: novatrust-design-system.md Section 6.1
 */
(function () {
  'use strict';

  function showError(field, message) {
    field.classList.add('has-error');
    field.setAttribute('aria-invalid', 'true');

    let errorEl = field.parentElement.querySelector('.field-error');

    if (!errorEl) {
      errorEl = document.createElement('div');
      errorEl.className = 'field-error';
      errorEl.innerHTML =
        '<span class="material-symbols-outlined" aria-hidden="true">error</span>' +
        '<span class="field-error-text"></span>';
      field.insertAdjacentElement('afterend', errorEl);
    }

    errorEl.querySelector('.field-error-text').textContent = message;
  }

  function clearError(field) {
    field.classList.remove('has-error');
    field.removeAttribute('aria-invalid');

    const errorEl = field.parentElement.querySelector('.field-error');
    if (errorEl) {
      errorEl.remove();
    }
  }

  function shakeField(field) {
    field.classList.remove('shake');
    void field.offsetWidth;
    field.classList.add('shake');
  }

  class FormValidator {
    constructor(form, rules) {
      this.form = form;
      this.rules = rules;
      this.hasErroredOnce = new Set();
      this.bind();
    }

    bind() {
      Object.keys(this.rules).forEach((name) => {
        const field = this.form.elements[name];
        if (!field) {
          return;
        }

        field.addEventListener('blur', () => this.validateField(name, field));

        field.addEventListener('input', () => {
          if (this.hasErroredOnce.has(name)) {
            this.validateField(name, field);
          }
        });
      });

      this.form.addEventListener('submit', (e) => {
        if (!this.validateAll()) {
          e.preventDefault();
        }
      });
    }

    validateField(name, field) {
      const rule = this.rules[name];
      const message = rule(field.value, field);

      if (message) {
        showError(field, message);
        this.hasErroredOnce.add(name);
        return false;
      }

      clearError(field);
      return true;
    }

    validateAll() {
      let firstInvalidField = null;
      let allValid = true;

      Object.keys(this.rules).forEach((name) => {
        const field = this.form.elements[name];
        if (!field) {
          return;
        }

        const valid = this.validateField(name, field);

        if (!valid) {
          allValid = false;
          if (!firstInvalidField) {
            firstInvalidField = field;
          }
        }
      });

      if (!allValid && firstInvalidField) {
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalidField.focus();
        shakeField(firstInvalidField);
      }

      return allValid;
    }
  }

  window.NovaValidation = { FormValidator, showError, clearError, shakeField };
})();
