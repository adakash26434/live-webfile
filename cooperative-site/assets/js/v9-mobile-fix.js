/* ============================================================
 * v9.11 — Mobile menu (stable drawer + dropdowns never navigate on mobile)
 * Public + Admin drawers. Plain delegation, no z-index wars.
 * Submenus toggle on parent-link tap (mobile only) when href === '#' or 'javascript:'
 * Otherwise the link navigates.
 * ============================================================ */
(function () {
  'use strict';

  /* ── Wrap wide tables for horizontal scroll ── */
  function wrapTables() {
    document.querySelectorAll('table').forEach(function (table) {
      if (table.closest('.v9-table-scroll, .dataTables_wrapper, .cke, .ndp-container, .id-card-wrap')) return;
      var firstRow = table.querySelector('tr');
      if (!firstRow) return;
      var cols = firstRow.querySelectorAll('th, td').length;
      if (cols < 3) return;
      var wrap = document.createElement('div');
      wrap.className = 'v9-table-scroll';
      table.parentNode.insertBefore(wrap, table);
      wrap.appendChild(table);
    });
  }

  /* ── Public site drawer ── */
  function attachPublicDrawer(toggleId, navId, closeId) {
    var toggle = document.getElementById(toggleId);
    var nav    = document.getElementById(navId);
    if (!toggle || !nav) return;
    if (toggle.dataset.v96Bound === '1') return;

    /* Match admin behavior: replace nodes to remove any pre-existing listeners */
    var freshToggle = toggle.cloneNode(true);
    toggle.parentNode.replaceChild(freshToggle, toggle);
    toggle = freshToggle;
    toggle.dataset.v96Bound = '1';

    var closeBtn = document.getElementById(closeId);
    if (closeBtn && closeBtn.parentNode) {
      var freshClose = closeBtn.cloneNode(true);
      closeBtn.parentNode.replaceChild(freshClose, closeBtn);
      closeBtn = freshClose;
    }
    var overlays = ['menuOverlay', 'pflMobileBackdrop']
      .map(function (id) { return document.getElementById(id); })
      .filter(Boolean);
    var savedScrollY = 0;

    function openNav() {
      savedScrollY = window.scrollY || document.documentElement.scrollTop || 0;
      nav.classList.add('nav-open', 'open', 'active');
      overlays.forEach(function (el) { el.classList.add('active'); });
      document.body.classList.add('mobile-nav-open');
      document.documentElement.classList.add('mobile-nav-open');
      document.body.style.top = '-' + savedScrollY + 'px';
      document.body.style.overflow = 'hidden';
      toggle.setAttribute('aria-expanded', 'true');
      nav.setAttribute('aria-hidden', 'false');
      setTimeout(function () {
        var first = nav.querySelector('#' + closeId + ', a, button');
        if (first && typeof first.focus === 'function') first.focus({ preventScroll: true });
      }, 30);
    }
    function closeNav() {
      nav.classList.remove('nav-open', 'open', 'active');
      overlays.forEach(function (el) { el.classList.remove('active'); });
      document.body.classList.remove('mobile-nav-open');
      document.documentElement.classList.remove('mobile-nav-open');
      document.body.style.top = '';
      document.body.style.overflow = '';
      toggle.setAttribute('aria-expanded', 'false');
      nav.setAttribute('aria-hidden', 'true');
      nav.querySelectorAll('.has-dropdown.open, .has-sub.open').forEach(function (el) {
        el.classList.remove('open');
      });
      nav.querySelectorAll('.dd-chevron-btn[aria-expanded="true"]').forEach(function (btn) {
        btn.setAttribute('aria-expanded', 'false');
      });
      if (savedScrollY) window.scrollTo(0, savedScrollY);
    }

    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (nav.classList.contains('nav-open') || nav.classList.contains('open') || nav.classList.contains('active')) {
        closeNav();
      } else {
        openNav();
      }
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeNav();
      });
    }
    overlays.forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeNav();
      });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeNav(); });

    /* Dedicated delegated chevron handler — robust on iOS/Safari overlays */
    nav.addEventListener('click', function (e) {
      if (window.innerWidth >= 992) return;
      var chev = e.target.closest('.dd-chevron-btn');
      if (!chev || !nav.contains(chev)) return;
      e.preventDefault();
      e.stopPropagation();
      var li = chev.closest('.has-dropdown, .has-sub');
      if (!li) return;
      li.classList.toggle('open');
      chev.setAttribute('aria-expanded', li.classList.contains('open') ? 'true' : 'false');
    }, true);

    /* ── Single delegated click handler on the nav ── */
    nav.addEventListener('click', function (e) {
      if (window.innerWidth >= 992) return;
      if (e.target.closest('.dd-chevron-btn')) return;
      var link = e.target.closest('a');
      if (!link || !nav.contains(link)) return;
      var li      = link.parentElement;
      var hasSub  = li && (li.classList.contains('has-dropdown') || li.classList.contains('has-sub'));
      if (hasSub) {
        /* Mobile UX: parent rows with submenu ALWAYS toggle; child links navigate. */
        e.preventDefault();
        e.stopPropagation();
        li.classList.toggle('open');
        var directBtn = li.querySelector(':scope > .dd-chevron-btn');
        if (directBtn) directBtn.setAttribute('aria-expanded', li.classList.contains('open') ? 'true' : 'false');
        return;
      }
      /* Leaf link → navigate, close drawer */
      setTimeout(closeNav, 50);
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth >= 992) closeNav();
    });
  }

  /* ── Admin sidebar drawer ── */
  function setupAdminSidebar() {
    var sidebar = document.getElementById('sidebar');
    var toggle  = document.getElementById('sidebarToggle');
    if (!sidebar || !toggle) return;
    if (toggle.dataset.v96Bound === '1') return;

    /* Replace toggle node to drop ANY pre-existing click listeners (e.g. admin.js) */
    var fresh = toggle.cloneNode(true);
    toggle.parentNode.replaceChild(fresh, toggle);
    toggle = fresh;
    toggle.dataset.v96Bound = '1';

    var overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'sidebar-overlay';
      document.body.appendChild(overlay);
    }
    var closeBtn = document.getElementById('sidebarClose');

    function openSidebar() {
      sidebar.classList.add('active');
      overlay.classList.add('active');
      document.body.classList.add('admin-nav-open');
    }
    function closeSidebar() {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
      document.body.classList.remove('admin-nav-open');
    }

    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      if (sidebar.classList.contains('active')) closeSidebar();
      else openSidebar();
    }, true); /* capture phase — fire before any bubbling listener */

    if (closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); closeSidebar(); });
    overlay.addEventListener('click', closeSidebar);
    window.addEventListener('resize', function () {
      if (window.innerWidth > 991) closeSidebar();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeSidebar();
    });

    /* Sidebar internal collapsible groups */
    sidebar.addEventListener('click', function (e) {
      var trigger = e.target.closest('.has-submenu > a, [data-toggle-submenu]');
      if (!trigger) return;
      var parent = trigger.parentElement;
      if (!parent || (!parent.classList.contains('has-submenu') && !trigger.dataset.toggleSubmenu)) return;
      var href = (trigger.getAttribute('href') || '').trim();
      if (!href || href === '#' || href.indexOf('javascript:') === 0) {
        e.preventDefault();
        parent.classList.toggle('open');
      }
    });
  }


  /* ── Inject per-item chevron toggle so parent <a> can navigate freely ── */
  function addDropdownChevrons() {
    document.querySelectorAll('.main-nav .has-dropdown, .main-nav .has-sub').forEach(function (li) {
      var link = li.querySelector(':scope > a');
      if (!link) return;
      var href = (link.getAttribute('href') || '').trim();
      var isToggleOnly = !href || href === '#' || href.indexOf('javascript:') === 0;
      if (isToggleOnly) return; /* already toggle-only, no extra button needed */
      if (li.querySelector('.dd-chevron-btn')) return; /* already added */
      /* Remove the inline chevron icon from the link text */
      var inlineChevron = link.querySelector('.fa-chevron-down');
      /* Create a dedicated chevron button */
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'dd-chevron-btn';
      btn.setAttribute('aria-label', 'Toggle submenu');
      btn.setAttribute('aria-expanded', li.classList.contains('open') ? 'true' : 'false');
      var label = (link.textContent || 'submenu').trim().toLowerCase().replace(/[^a-z0-9\u0900-\u097f]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 48) || 'submenu';
      btn.setAttribute('data-testid', 'public-mobile-submenu-toggle-' + label);
      btn.innerHTML = '<i class="fas fa-chevron-down"></i>';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        li.classList.toggle('open');
        btn.setAttribute('aria-expanded', li.classList.contains('open') ? 'true' : 'false');
      });
      if (inlineChevron) inlineChevron.style.display = 'none';
      li.insertBefore(btn, link.nextSibling);
    });
  }

  function init() {
    wrapTables();
    /* Always bind whichever nav exists — both can coexist on transition pages */
    if (document.getElementById('mobileMenuToggle2') && document.getElementById('mainNavV2')) {
      attachPublicDrawer('mobileMenuToggle2', 'mainNavV2', 'closeMenuV2');
    }
    if (document.getElementById('mobileMenuToggle') && document.getElementById('mainNav')) {
      attachPublicDrawer('mobileMenuToggle', 'mainNav', 'closeMenu');
    }
    addDropdownChevrons();
    setupAdminSidebar();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
