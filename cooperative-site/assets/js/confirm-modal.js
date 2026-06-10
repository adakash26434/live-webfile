/**
 * ════════════════════════════════════════════════════════════
 * COOP CONFIRM MODAL — Global Confirm Dialog (v1.0)
 * ════════════════════════════════════════════════════════════
 *
 * Replaces native browser `confirm()` dialogs with a styled
 * Bootstrap modal. Zero changes required on existing pages —
 * simply add `data-confirm="message"` attribute to forms or
 * buttons to opt in.
 *
 * HOW IT WORKS
 * ─────────────
 * 1. On ANY form with `data-confirm="..."`:
 *    - Intercepts submit event
 *    - Shows modal with the confirm message
 *    - On "Confirm" click → re-submits form programmatically
 *    - On "Cancel" → nothing (form stays)
 *
 * 2. On ANY button/link with `data-confirm="..."` + `data-confirm-href="url"`:
 *    - Intercepts click
 *    - Shows modal
 *    - On confirm → navigates to href
 *
 * 3. Legacy `onsubmit="return confirm('...')"` patterns still work
 *    (native browser confirm) — migration is gradual.
 *
 * USAGE EXAMPLES
 * ─────────────
 *  <form method="POST" data-confirm="के तपाईं यो रेकर्ड मेटाउन निश्चित हुनुहुन्छ?">
 *    ...
 *  </form>
 *
 *  <a href="delete.php?id=5"
 *     data-confirm="के तपाईं पक्का मेटाउन चाहनुहुन्छ?"
 *     data-confirm-href="delete.php?id=5">मेटाउनुहोस्</a>
 *
 *  <button data-confirm="सबैलाई पठाउने?" data-confirm-form="myFormId">
 *    पठाउनुहोस्
 *  </button>
 *
 * ADVANCED: Custom icon + variant
 *  <form data-confirm="सावधान!" data-confirm-icon="fa-warning" data-confirm-variant="warning">
 * ════════════════════════════════════════════════════════════
 */
(function () {
  'use strict';

  const MODAL_ID   = 'coopGlobalConfirmModal';
  const MODAL_HTML = `
<div class="modal fade" id="${MODAL_ID}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header py-3" id="${MODAL_ID}Header">
        <h6 class="modal-title fw-semibold d-flex align-items-center gap-2" id="${MODAL_ID}Title">
          <i class="fas fa-circle-question" id="${MODAL_ID}Icon"></i>
          <span id="${MODAL_ID}TitleText">पुष्टि गर्नुहोस्</span>
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="रद्द"></button>
      </div>
      <div class="modal-body py-3">
        <p class="mb-0 small" id="${MODAL_ID}Body">के तपाईं निश्चित हुनुहुन्छ?</p>
      </div>
      <div class="modal-footer py-2 gap-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" id="${MODAL_ID}CancelBtn">
          रद्द गर्नुहोस्
        </button>
        <button type="button" class="btn btn-sm btn-danger" id="${MODAL_ID}ConfirmBtn">
          <i class="fas fa-check me-1"></i>पुष्टि गर्नुहोस्
        </button>
      </div>
    </div>
  </div>
</div>`;

  // Variant config
  const VARIANTS = {
    danger:  { headerClass: 'bg-danger text-white',   btnClass: 'btn-danger',   icon: 'fa-circle-exclamation' },
    warning: { headerClass: 'bg-warning',             btnClass: 'btn-warning',  icon: 'fa-triangle-exclamation' },
    success: { headerClass: 'bg-success text-white',  btnClass: 'btn-success',  icon: 'fa-circle-check' },
    primary: { headerClass: 'bg-primary text-white',  btnClass: 'btn-primary',  icon: 'fa-circle-question' },
    info:    { headerClass: 'bg-info text-white',     btnClass: 'btn-info',     icon: 'fa-circle-info' },
    default: { headerClass: '',                       btnClass: 'btn-danger',   icon: 'fa-circle-question' },
  };

  let _modal       = null;   // Bootstrap modal instance
  let _pendingCb   = null;   // callback to run on confirm

  function ensureModal() {
    if (document.getElementById(MODAL_ID)) return;
    const tmp = document.createElement('div');
    tmp.innerHTML = MODAL_HTML;
    document.body.appendChild(tmp.firstElementChild);

    const el = document.getElementById(MODAL_ID);
    _modal = new bootstrap.Modal(el);

    document.getElementById(MODAL_ID + 'ConfirmBtn').addEventListener('click', function () {
      _modal.hide();
      if (typeof _pendingCb === 'function') {
        _pendingCb();
        _pendingCb = null;
      }
    });
  }

  /**
   * Show the global confirm modal.
   * @param {string} message  - Confirm message body
   * @param {Function} onConfirm - Callback on confirmation
   * @param {Object} opts     - { title, icon, variant }
   */
  window.coopConfirm = function (message, onConfirm, opts) {
    opts = opts || {};
    ensureModal();

    const variant = VARIANTS[opts.variant] || VARIANTS.default;
    const icon    = opts.icon || variant.icon;

    // Update modal content
    const headerEl  = document.getElementById(MODAL_ID + 'Header');
    const iconEl    = document.getElementById(MODAL_ID + 'Icon');
    const titleEl   = document.getElementById(MODAL_ID + 'TitleText');
    const bodyEl    = document.getElementById(MODAL_ID + 'Body');
    const confirmEl = document.getElementById(MODAL_ID + 'ConfirmBtn');

    // Reset header classes
    headerEl.className = 'modal-header py-3';
    if (variant.headerClass) headerEl.className += ' ' + variant.headerClass;

    // Close button tint
    const closeBtn = headerEl.querySelector('.btn-close');
    if (variant.headerClass && variant.headerClass.includes('text-white')) {
      closeBtn.classList.add('btn-close-white');
    } else {
      closeBtn.classList.remove('btn-close-white');
    }

    iconEl.className    = 'fas ' + icon;
    titleEl.textContent = opts.title || 'पुष्टि गर्नुहोस्';
    bodyEl.textContent  = message   || 'के तपाईं निश्चित हुनुहुन्छ?';

    // Confirm button variant
    confirmEl.className = 'btn btn-sm ' + variant.btnClass;

    _pendingCb = onConfirm;
    _modal.show();
  };

  // ── Auto-wire: forms with data-confirm ──────────────────────────────────
  document.addEventListener('submit', function (e) {
    const form = e.target;
    const msg  = form.getAttribute('data-confirm');
    if (!msg) return;

    // Already confirmed — let it pass
    if (form.dataset.coopConfirmed === '1') {
      form.dataset.coopConfirmed = '0';
      return;
    }

    e.preventDefault();
    const variant = form.getAttribute('data-confirm-variant') || 'danger';
    const icon    = form.getAttribute('data-confirm-icon')    || null;
    const title   = form.getAttribute('data-confirm-title')   || 'पुष्टि गर्नुहोस्';

    window.coopConfirm(msg, function () {
      form.dataset.coopConfirmed = '1';
      form.submit();
    }, { title: title, variant: variant, icon: icon });
  }, true);

  // ── Auto-wire: links / buttons with data-confirm-href ───────────────────
  document.addEventListener('click', function (e) {
    const el   = e.target.closest('[data-confirm][data-confirm-href]');
    if (!el) return;

    e.preventDefault();
    const msg     = el.getAttribute('data-confirm');
    const href    = el.getAttribute('data-confirm-href');
    const variant = el.getAttribute('data-confirm-variant') || 'danger';
    const icon    = el.getAttribute('data-confirm-icon')    || null;
    const title   = el.getAttribute('data-confirm-title')   || 'पुष्टि गर्नुहोस्';

    window.coopConfirm(msg, function () {
      window.location.href = href;
    }, { title: title, variant: variant, icon: icon });
  }, true);

})();
