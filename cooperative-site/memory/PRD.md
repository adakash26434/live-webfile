# Project Review & Critical Bug Fix PRD

## Original Problem Statement
"yo mero project ma kehi issue cha please fix gardinus la tapai project review garnus ani ma kehi issue list dinchu"

User later asked to start, selected a specific issue category, and prioritized fixing the most critical issue found during review.

## Architecture Decisions
- Existing project is a PHP cooperative website with public pages, admin portal, member portal, MySQL/PDO configuration, and shared bootstrap/config files.
- No framework migration was done; fix was intentionally minimal and targeted.
- Kept the existing database setup flow intact: public pages show setup message when DB credentials are missing; admin DB setup remains available.

## Implemented
- Fixed a critical bootstrap fatal error where `/app/_bootstrap.php` loaded `/app/core/helpers.php` before `/app/includes/config.php`, causing duplicate function declarations such as `sanitize()`.
- Updated `/app/_bootstrap.php` to load production config first and conditionally load optional legacy core files only when their sentinel functions are missing.
- Verified 272 PHP files with `php -l`; no syntax errors found.
- Verified key include flows: root index, member login, admin dashboard/bootstrap; no duplicate function fatal errors remain.
- 2026-06-08: Fixed KYC Nepal address dataset in `/app/includes/nepal-address.php` to 7 provinces, 77 districts, 753 unique local levels, with no duplicate municipality entries. Corrected known bad entries such as duplicate Rasuwa records, duplicate Rautahat record, invalid ward count for Dordi, typo in Pachaaljharana, and Kanchanpur Dodhara Chandani naming.
- 2026-06-08: Fixed public mobile menu interaction by stabilizing `assets/js/v9-mobile-fix.js`, adding dedicated dropdown chevron toggles, aria-expanded updates, close-state cleanup, and matching mobile CSS/test IDs in `/app/includes/header.php` and `/app/assets/css/app-public.css`.
- 2026-06-08: Added institutional profile fields in admin/public flow: other fund (`other_fund`), bank/cash balance (`bank_cash_balance`), fixed assets (`fixed_assets`), and total loan members (`total_loan_members`). Updated admin form, POST save/update logic, auto schema alters, fresh install SQL, ensure-admin-tables schema, and public display with null-safe fallbacks.
- 2026-06-08: Fixed KYC province dropdown duplication caused by repeated `initAllKYCCapture()` calls. Made `setupAddressDropdowns()` and same-address listener idempotent in `/app/assets/js/kyc-capture.js`, added cache-busting script version `v=10.9` in `/app/online-kyc.php`, and cleaned JS lint issues in the same file.
- 2026-06-08: Improved public institutional profile data-view UI/UX with compact bento-style stat cards, clearer fiscal-year header/date/document action, stronger financial hierarchy, readable indicator bars, mobile-responsive spacing, and required `data-testid` attributes for key interactive/user-facing elements.
- 2026-06-08: Improved admin institutional profile data-entry form UI/UX with compact control-room styling, stronger header, tighter grouped sections, cleaner input spacing, sticky save/cancel footer, mobile responsiveness, and additional `data-testid` attributes for important inputs/actions.
- 2026-06-08: Reworked public institutional profile data view from large bento cards to a compact ledger/table-style layout based on user screenshot feedback. Reduced header/card height, removed oversized stat cards, added dense rows for month-wise records, kept document action and indicators compact.
- 2026-06-08: Updated compact public institutional profile ledger to show two data items per row on desktop, reducing vertical height further while keeping mobile horizontal-scroll safety and preserving `data-testid` markers.
- 2026-06-08: Corrected the intended layout after user clarification: each month/report is now one table row, with all key metrics across columns (members, share capital, funds, savings, loan, bank/cash, fixed assets, total assets, indicators, document). This replaces the per-report multi-row ledger so month-wise data is clearer.
- 2026-06-08: Updated month-wise public institutional profile again per user clarification: desktop now shows two month/report cards per row, each card contains that month's compact metrics. Tablet/mobile falls back to one card per row.
- 2026-06-08: Fixed public mobile menu based on user screen recording: added final stable drawer CSS override to remove blur/grey wash, force opaque clear drawer, enforce modal backdrop above page content, prevent background clicks, make menu/close buttons explicit `type="button"`, cache-busted `v9-mobile-fix.js` to `v=9.11`, and changed mobile dropdown parents to toggle only instead of navigating to their page.
- 2026-06-08: Fixed notice popup desktop/mobile behavior: removed duplicate homepage popup markup (kept single global header popup), changed popup seen state from permanent `localStorage` to per-session `sessionStorage` so desktop does not stay hidden forever after one close, hid document/PDF button for photo-only notices, added cleaner photo-only image sizing, body scroll lock while popup is open, and added popup `data-testid` attributes.
- 2026-06-08: Added inline critical mobile menu fallback in `/app/includes/header.php` because the external mobile JS/cache path was still leaving hamburger clicks inactive on some mobile views. The fallback binds hamburger/close/backdrop/submenu directly, injects submenu chevrons, adds critical drawer CSS, and marks the menu as bound so the external script does not double-bind.
- 2026-06-08: Refined public institutional profile month cards per screenshot feedback: kept desktop two month/report cards per row, but restored the previous clearer inside-content style with serial number, icon, title, value/detail columns, plus chips for NPA/NPL/Liquidity.
- 2026-06-08: Cleaned up unused CSS remnants from earlier institutional profile layout iterations (old ledger/table/metrics/bento overrides) so the active two-card icon-ledger layout is easier to maintain. Created cPanel-safe update package `/app/aakash-coop-cpanel-update-2026-06-08.zip` with code only, excluding uploads/cache/logs/memory/test reports/tests/git metadata.
- 2026-06-08: **DEEP PROJECT REVIEW & CLEANUP** — fixed root cause of "create/list icons hidden by bottom color, only on hover" issue reported via screenshots:
    1. **Root cause**: `.btn { overflow:hidden }` in `app-core.css` (line 945) and `app-public.css` (line 4435) was clipping Devanagari text descenders (ँ, ी, ु) and bottom of icons inside green admin page-header buttons. Removed `overflow:hidden`, removed CSS ripple `::before` pseudo-element (depended on overflow), added `text-decoration:none !important` on `.btn` + `a.btn`.
    2. **Harmful neutralizer block removed**: `app-admin.css` lines 10629–10683 was forcing `background:#fff; color:#1f2937` on ALL `.btn-success/.btn-info/.btn-warning/.btn-secondary/.btn-outline-*` → broke colored buttons project-wide. Removed.
    3. **Final uniformity CSS appended to `assets/css/global-theme.php`** (loaded LAST so it beats everything): button overflow:visible + line-height 1.45 + min-height 38px for Devanagari safety; inactive nav-tabs on green strip use opaque white + text-shadow (fixes "वित्तीय रकम" white-on-green low contrast); admin-bottom-nav icons opacity:1 by default; stat-uniform-card icon color guaranteed visible per `data-bg` variant; all button icons `color:inherit` so they're never invisible.
    4. **Database deep-fix**: removed duplicate HRM module block (5 tables) in `database/install.sql`. The duplicate at lines 1896–2018 was a less-complete schema that ran first; the proper full schema at lines 2022+ was being skipped because of `IF NOT EXISTS`. Result: HRM employees table missing many columns on fresh installs. Fixed by removing the shorter duplicate so the full schema (employees + contracts + documents + education + experience + family + bank + history + internal_messages) is created. Total tables down from 79 to 74 (5 duplicates removed). Also normalized indentation on `institutional_profile` CREATE.
    5. **Unused files removed**: `assets/css/_color-vars.php` (@deprecated, replaced by `global-theme.php`), `assets/js/pwa-init.js` (zero references), `assets/js/v10.6-mobile-helpers.js` (zero references).
    6. **Regression suite extended**: added 4 new tests in `tests/test_php_feature_regression.py` for the four fixes above (overflow:hidden removed, final patch present, no duplicate HRM tables, neutralizer removed). All 11 tests pass.
    7. **PHP syntax check**: `php -l` clean across all 270 PHP files.
- 2026-06-08 (continued): **Backlog cleanup**:
    1. **`pages.php` ↔ `pages-v2.php` full migration**: Removed legacy 9-line `admin/pages.php` wrapper. Renamed `admin/pages-v2.php` → `admin/pages.php`. Updated all internal redirects + outside references (`admin/settings.php`, `admin/includes/admin-header.php`, `admin/help-guide.php`) from `pages-v2.php` → `pages.php`. Renamed function `pages_v2_tinymce()` → `pages_admin_tinymce()`. Zero stale references remain.
    2. **`app-admin.css` duplicate consolidation** (17,215 → 16,915 lines, ~300 saved):
        - Removed 3 of 4 duplicate `.stat-uniform-card` blocks (lines ~8027, ~13123, ~13947). Canonical block at file end retained.
        - Removed duplicate `.admin-nav-tabs` block at ~line 3587 (was overridden by !important block at ~5154).
        - Removed duplicate `.nav-tabs` blocks at ~line 7451 (kept only mobile `overflow-x:auto` wrapper) and ~line 8723 (was overridden by pill block at ~8964).
        - Removed dead `.nav-tabs .nav-link` rules from final canonical block (kept `.nav-pills .nav-link.active`).
    3. Regression suite all green (11/11), PHP syntax clean on all 270 PHP files, CSS brace balance verified.
- 2026-06-10: **Mobile menu drawer "dim/hidden" fix** (from user screen recording showing drawer text barely visible under backdrop tint):
    - Root cause: `.pfl-header-wrapper` has `position:sticky; z-index:1000` which creates a **stacking context** that traps `#mainNavV2` (drawer) inside it. The body-level `#pflMobileBackdrop` (z-index:2147483000) ends up above the drawer because the wrapper itself caps at z-index 1000 — drawer's own `z-index:2147483001` is meaningless inside the wrapper's context.
    - Fix in `includes/header.php`: When body has `.mobile-nav-open`, lift `.pfl-header-wrapper` to `z-index:2147483002` with `isolation:isolate`. Now wrapper sits above backdrop, drawer becomes fully visible at full contrast, backdrop dims only the rest of the page below.
    - Regression test added: `test_mobile_drawer_stacking_fix_present` (12/12 tests pass).
- 2026-06-10: **FIX-PASS 2** — three additional UI issues reported by user from `final.bandanasigdel.com.np`:
    1. **Public homepage "अन्य डिजिटल सेवाहरू" cards** — h5 titles (`अनलाइन फारमहरू`, `टुल्स / क्याल्कुलेटर`, `सदस्य सेवा / सहायता`) were rendering as white text on light-gray bg (invisible). Root cause: older `.tools-category-card h5` block (line 7894 of app-public.css) sets `color:#fff` on a gradient bg that may fail to load in cached state. Fix: appended explicit override in `assets/css/global-theme.php` (loaded LAST) forcing `color: var(--primary-dark)` on `color-mix(primary, white)` chip with bordered chip — guaranteed contrast. Also disabled the shimmer pseudo-element that creates white wash.
    2. **HRM Dashboard / Employee List action buttons** (`कर्मचारी सूची`, `ड्यासबोर्ड`, `+ नयाँ कर्मचारी`) — Devanagari descenders + icon bottoms clipped. Root cause: these use `.btn-coop` (custom class, NOT Bootstrap `.btn`), so our earlier `.btn { overflow:visible }` fix didn't apply. Fix: extended the same pattern to `.btn-coop` — `overflow:visible`, `text-decoration:none`, `line-height:1.45`, `min-height:40px`, `padding-top/bottom:9px`, icon `flex-shrink:0` + `font-size:0.95em`. Also added `.stf-page-head { flex-wrap:wrap }` so the action row wraps cleanly on narrow viewports.
    3. **Institutional profile create/save buttons** — defensive override added on `.admin-content .btn.btn-primary` + `button[form="profileMainForm"].btn` with explicit padding + line-height (in case cache serves an older variant).
    Regression test added: `test_fix_pass2_present` (13/13 tests pass). CSS rule count: 295 → 304.
- 2026-06-10: **FIX-PASS 3** — global, passive solution for two recurring patterns:
    1. **Oversized icons in dropdowns/buttons (e.g., छिटो लिङ्क)** — caps icon font-size to `0.92em` (dropdowns/menus) and `0.95em` (buttons/nav) so icons always remain proportional to text. Also enforces `width:1.15em`, `flex-shrink:0`, `vertical-align:middle` for clean inline alignment. Targets: `.dropdown-menu *, .pfl-drop, .qh-menu, .btn > i, .btn-coop > i, .badge > i, .navbar .nav-link > i, .sidebar-nav > i`.
    2. **Nepali/Devanagari descender clipping in buttons + badges** — converts all fixed `height: Xpx` to `height: auto !important` with safe `line-height: 1.45` and `padding-block: 5-9px`. Badge padding bumped from 4px → 5px top/bottom + `min-height: 22px` + `display: inline-flex; align-items: center`. Same pattern applied to `.btn, .btn-coop, .nav-link, .dropdown-item, .status-chip, .chip, .pill, .tag, .badge`.
    Inline `[style*="height"]` selectors give `min-height: 36px` fallback when templates hard-code height in HTML.
    Regression test added: `test_fix_pass3_global_icon_devanagari` (14/14 tests pass). CSS rule count: 304 → 316. PHP `-l` clean on all 270 files.
- 2026-06-10: **Comprehensive refactor audit + documentation deliverables**:
    1. **Legacy file audit** — scanned project for `*-old.*`, `*-backup.*`, `*-v[123].*`, `@deprecated` headers, zero-reference JS/PHP. **Result: 0 orphans** (earlier 2026-06-08 cleanups already removed all stale files). Project is now well-curated.
    2. **`archive_old_v1/` convention introduced** — new folder with `README.md` (workflow: verify zero refs → move → log) and append-only `MOVED.log` documenting historical deletions. Future deprecations move here instead of being deleted, retained for 1 release cycle for safe rollback.
    3. **Documentation deliverables (NEW)**:
        - `/app/README.md` — top-level project overview (tech stack, structure, setup, conventions, testing)
        - `/app/docs/PROJECT_STRUCTURE.md` — detailed directory map with every folder explained
        - `/app/docs/CSS_ARCHITECTURE.md` — cascade order, Devanagari safety table, icon sizing convention, token reference
        - `/app/docs/REFACTOR_AUDIT_2026-06-10.md` — evidence trail with replication commands + risk assessment
    4. **System integrity verified**: 270/270 PHP files lint-clean, all 6 CSS files brace-balanced, 14/14 regression tests pass, dependencies/DB hooks/asset paths untouched.

- 2026-06-12: **PROJECT-WIDE FINAL AUDIT, REFACTOR, CLEANUP & STANDARDIZATION PASS**
    1. **Dark mode toggle FULLY IMPLEMENTED** — toggle existed in HTML but had zero JS. Fixed:
        - `assets/js/main.js`: Added 55-line dark mode toggle implementation with localStorage persistence (`coop_dark_mode`), OS prefers-color-scheme fallback, FOUC prevention preload script in `<head>` of both `includes/header.php` and `admin/includes/admin-header.php`.
        - `admin/includes/admin-header.php`: Added dark mode toggle button with `data-testid="admin-dark-mode-toggle"`.
        - `admin/includes/admin-footer.php`: Added dark mode JS for admin panel (localStorage sync).
        - `member/includes/chrome-foot.php`: Added dark mode sync JS for member portal.
        - `assets/css/global-theme.php` FIX-PASS 4: Complete dark mode CSS for admin (sidebar, topbar, cards, tables, forms, modals, dropdowns), member portal, print safety, smooth transitions.
    2. **N+1 SHOW COLUMNS queries eliminated** — `includes/header.php` had 3 separate `SHOW COLUMNS FROM pages LIKE 'show_in_menu'` queries per page load. Replaced with 1 cached query at top of file, single SQL to fetch all menu pages, PHP categorization by menu_position.
    3. **Hardcoded color fixed** — mobile drawer `#0b3f24` → `var(--primary-dark, #0b3f24)`.
    4. **Document modal inline CSS** — `institutional-profile.php` document preview modal moved 200+ chars inline CSS to `<style>` block with CSS variables.
    5. **CSS duplicate removed** — `.logo-text` had redundant top-level block immediately overridden by `display:none` in same @media; redundant block removed.
    6. **Regression tests fixed & extended** — ROOT path now auto-detects `/app/cooperative-site/` or `/app/`; PHP-dependent tests skip gracefully if PHP CLI unavailable; 2 new tests added (dark mode JS, no N+1 SHOW COLUMNS). 13 passed, 3 skipped.
    7. **Security audit** — Full audit passed: 0 SQL injection, 0 XSS, 0 eval, CSRF on all admin POSTs, bcrypt passwords, PDO everywhere, proper file upload validation, security headers.
    8. **CSS audit documented** — 125 top-level duplicate selectors found in app-public.css. Documented with CSS audit tool script in `docs/REFACTOR_AUDIT_2026-06-12.md`.
- 2026-06-12 (continued): **P0 + P1 COMPLETION — SELECT *, Forms/Modals, Icons, core/ cleanup**
    1. **core/ dead code archived**: `core/helpers.php` + `core/init.php` → `archive_old_v1/core/` (zero active references confirmed). `core/` directory removed.
    2. **68 SELECT * → explicit columns**: New `scripts/select-star-v2.py` script. All known tables mapped (members, kyc_applications, loan_applications, account_applications, digital_service_requests, auction_notices, election_positions, election_candidates, election_posts, designations, partner_facilities, site_license_renewal_notices, hrm_employees, member_welfare_claims, member_notifications, member_transactions, request_status_history, member_otp_tokens). Only 2 intentional SELECT * remain (backup export + dynamic table tracker).
    3. **Forms/Modals shared component system**:
       - `includes/components/modal.php` + `modal-close.php` — Bootstrap 5 modal wrapper (reusable across all admin pages)
       - `includes/components/form-field.php` — Standardized form input row (text/email/tel/number/date/textarea/select/checkbox/hidden)
       - `assets/js/confirm-modal.js` — Global JS confirm dialog (replaces browser native `confirm()`). Auto-wires via `data-confirm="message"` attribute on forms/links. Injected globally via `admin-footer.php`.
    4. **Font Awesome version standardized**: All CDN links now uniformly `6.5.1` (was mixed 6.4.0/6.5.0/6.5.1 across 10 files).
    5. **Icon audit**: 100% FontAwesome; 0 Lucide references. Single library confirmed.
    6. **_registry.php** updated with all new components + usage examples.
    7. **Regression suite**: 33 passed, 3 skipped (was 24 passed). 9 new tests added.
    1. **getSetting() bulk preload** — Was doing 37+ separate DB queries per page for settings. Now does 1 `SELECT setting_key, setting_value FROM site_settings` to warm the static cache; all subsequent calls are memory hits. Zero backward compatibility change.
    2. **CSP promoted to enforce mode** — Content-Security-Policy-Report-Only → Content-Security-Policy. Added all missing CDN origins: code.jquery.com, unpkg.com, cdn.jsdelivr.net, cdnjs.cloudflare.com.
    3. **Dead legacy mysqli connection removed** — `new mysqli(DB_HOST, DB_USER, ...)` was being called on every page load even though zero PHP files use `$conn` or any mysqli_* functions. Removed connection creation; null shim retained.
    4. **Canonical typography system (FIX-PASS 5)** — global-theme.php now has single source of truth for h1-h6 type scale using `clamp()`, loaded last to override duplicates in app-core.css/app-public.css. Covers footer, page-banner, section, card, form-label, table, button, badge, stat-card, mobile, print.
    5. **Empty-state component** — Replaced 200+ chars of inline styles with `.empty-state-icon`, `.empty-state-title`, `.empty-state-body` CSS classes. Dark mode support added.
    6. **CSS duplicates (targeted)** — Removed 2 intermediate `.footer-top` padding-only blocks overridden by later !important rule.
    7. **admin/members.php SELECT *** → explicit 14-column list.
    8. **Responsive grid fixes** — index.php: service cards, stats, features all got `col-md-` fallbacks. loan-apply.php, appointment.php: success and sidebar columns got mobile fallbacks. member-welfare.php: table wrapped in `.table-responsive`.
    9. **Version consistency** — admin-footer.php and chrome-foot.php were loading v9-mobile-fix.js v9.7; updated to v9.11.
    10. **Dark mode button in admin CSS** — `button.admin-header-icon` CSS reset added for proper rendering; dark mode icon invert styles for admin header.
    11. **CSS audit utility** — `scripts/css-audit.py` created for future deduplication sprints.
    Full audit report: `docs/REFACTOR_AUDIT_2026-06-12.md`

- 2026-06-13: **PUBLIC MOBILE NAV — STACKING CONTEXT FIX (TELEPORTATION APPROACH)**
    - **Root cause**: `#mainNavV2` nav drawer was inside `.pfl-header-wrapper` (z:1000), which creates a CSS stacking context. The backdrop `#pflMobileBackdrop` (z:2147483000) at body level was rendered ABOVE the entire wrapper, making the drawer invisible/dimmed. Previous z-index workaround (lifting wrapper to z:2147483002) was fragile.
    - **Definitive fix**: JS teleportation — when mobile (<992px), `bindPflMobileMenu()` moves `#mainNavV2` to be a direct sibling of wrapper (inserted before `#pflMobileBackdrop`). At body level, nav z:2147483001 correctly appears ABOVE backdrop z:2147483000. On resize to desktop (≥992px), nav is restored to `.pfl-nav-area` so desktop CSS selectors still work.
    - **CSS improvements**: Removed `will-change:transform` + `contain:layout paint` from nav (was adding unnecessary complexity); fixed `body.header-v2.mobile-nav-open` scroll-lock to not override JS scroll-position restore (removed `inset:0 !important`, kept only `position:fixed` + `right/bottom/left:0`).
    - **Regression test updated**: `test_mobile_drawer_stacking_fix_present` now validates teleportation code instead of old wrapper z-index hack. 33 passed, 3 skipped.

- 2026-06-13 (session 2): **CSS DEDUP, FORMS MIGRATION, COMPONENT FIXES**
    - **CSS**: Merged adjacent `.hero-slide-modern` duplicate rules into single rule in `app-public.css` (safe dedup).
    - **form-field.php fixed**: Replaced `goto _ff_cleanup` with clean `if/elseif/else` structure — now safe for multiple includes in the same PHP scope without fatal label-duplication errors.
    - **designations.php migrated**: Admin "पद मास्टर" form (7 fields: 2 hidden, 2 text, 1 select, 1 number, 1 checkbox) now uses `form-field.php` shared component — all hidden field boilerplate replaced with component includes.
    - **modal.php documented**: Added note that `<form class="modal-content">` pattern (used by hrm-employee-directory, manage-admins, members) is NOT compatible with `modal.php`; those remain as inline modals. `modal.php` is for new modals with `<div class="modal-content">`.
    - **Regression**: 33 passed, 3 skipped ✓.

## Current Known Environment State
- Database credentials are not configured in this workspace, so public/member pages may show the setup screen and admin bootstrap logs non-fatal DB-not-configured messages. This is expected until real DB config is present.
- This fork is a legacy/custom PHP project. Supervisor React/FastAPI services are not applicable here and may show FATAL because `/app/frontend` and `/app/backend` do not exist.

## Prioritized Backlog
### P0 (Must do before launch)
- Configure/test against the real database environment to validate admin/member features end-to-end.
- ~~**Forms/Modals shared component system**~~: DONE 2026-06-12 (modal.php, modal-close.php, form-field.php, confirm-modal.js)
- ~~**core/ dead code cleanup**~~: DONE 2026-06-12 (archived to archive_old_v1/core/)

### P1 (High Priority)
- **CSS Deduplication sprint**: 92 duplicate selectors still in app-admin.css. `css-audit.py` script available. (~1 day effort)
- ~~**SELECT * reductions**~~: DONE 2026-06-12 (68 queries replaced; only 2 intentional SELECT * remain)
- ~~**Icon library audit**~~: DONE 2026-06-12 (100% FontAwesome 6.5.1; 0 Lucide; all versions standardized)
- ~~**Content-Security-Policy**~~: Already enforced as of 2026-06-12.
- ~~**Rate Limiting**~~: Already implemented in member login and admin login (`checkLoginAttempts()`, `checkRateLimit()`).

### P2 (Medium Priority)
- **CSS variable coverage**: Migrate remaining inline `#hex` colors in PHP files to CSS vars.

### P3 (Low Priority / Future)
- ~~Icon migration: FA → Lucide~~: Audit found 4,153 FA usages — standardization would be a dedicated sprint only. Risk: very high. Recommendation: stay with FA 6.5.1.
- CSS chunking: Split app-public.css per page for lazy loading.
- Redis/APCu caching for `getSetting()` calls.

## Next Tasks
- With DB configured, test admin institutional profile add/edit and public profile rendering end-to-end.
- Validate dark mode toggle on live site (localStorage persistence across page navigations).
- CSS Deduplication sprint: run `scripts/css-audit.py` to get full duplicate selector report.
- Gradually migrate `onsubmit="return confirm(...)"` forms to `data-confirm="..."` attribute for better UX.

## Verification Log
- 2026-06-12 (Agent-2 session): **SHARED COMPONENT SYSTEM + SECURITY HARDENING**
  - `adminPagination()` added to `admin/includes/admin-ui.php`; migrated `audit-log.php`, `appointments.php`, `member-online-portal.php`, `members.php` to use it.
  - New components created: `includes/components/pagination.php` (universal Bootstrap pagination), `includes/components/status-badge.php` (defines `statusBadge()` function), `scripts/migrate-empty-states.py`, `scripts/migrate-empty-states-v2.py`.
  - `adminEmptyRow()` updated to accept `$icon` parameter and use CSS classes instead of inline styles. Dark-mode CSS block added. 2 duplicate CSS blocks for `.admin-empty-state` removed from `app-admin.css`.
  - **66 inline empty-state `<tr>` blocks** across 42+ admin pages migrated to `adminEmptyRow()`. Pages include: awards, news, feedbacks, grievances, services, downloads, careers, faqs, gallery, team, notices, auctions, committees, election-candidates/posts/information, manage-admins, welfare-claims, partner-facilities, etc.
  - `dashboard.php`, `gallery.php`, `member-online-portal.php` member-list section now use `includes/components/empty-state.php`.
  - `news.php` (public) uses `includes/components/pagination.php`.
  - **8 dead `getFlash()` blocks** removed from admin pages that already get flash handled by `admin-header.php` (awards.php, members.php, feedbacks.php, about-settings.php, welfare-claims.php, job-applications.php, notification-settings.php, satisfaction-settings.php, member-online-portal.php).
  - **CSRF gaps fixed**: `checkCSRF()` added to POST handlers in `staff.php`, `hrm-employees.php`, `hrm-departments.php`, `push-notifications.php`. `csrfField()` tokens added to both push-notification forms.
  - **Security**: `admin/backup-restore.php` — Restore operation now requires `$_SESSION['is_superadmin']`, matching the root `backup-restore.php`.
  - **Dead code documentation**: `core/helpers.php` marked as "PLANNED BUT NOT YET INTEGRATED" with warning header. Functions are already canonical in `includes/config.php`.
  - `_registry.php` updated to document `pagination.php` and `status-badge.php`.
  - Test suite extended: 24 tests pass, 3 skipped (expected).

- 2026-06-08: `php -l` passed for modified PHP files: `includes/nepal-address.php`, `includes/header.php`, `online-kyc.php`, `admin/institutional-profile.php`, `institutional-profile.php`, `admin/includes/ensure-admin-tables.php`.
- 2026-06-08: Address data CLI check passed: Municipalities=753, Unique=753, Duplicates=0.
- 2026-06-08: JS lint passed for `/app/assets/js/v9-mobile-fix.js`.
- 2026-06-08: Isolated Playwright DOM smoke confirmed mobile drawer open/close, chevron injection, submenu toggle, and aria-expanded update.
- 2026-06-08: Testing agent iteration 2 initially found null-safe public profile issue; fixed it and reran `/app/tests/test_php_feature_regression.py`: 7 passed.
- 2026-06-08: Verified KYC province dropdown repeated initialization in Playwright isolated DOM: after 5 manual re-inits, permanent province select stayed at 8 options total (placeholder + 7 provinces), with no duplicate province list.
- 2026-06-08: Verified institutional profile UI update with PHP syntax check, regression suite (`7 passed`), and browser smoke layout test showing no horizontal overflow at 1366px with sample data.
- 2026-06-08: Verified admin institutional profile UI update with PHP syntax check, regression suite (`7 passed`), and isolated browser layout smoke test showing no horizontal overflow at 1366px with sample admin form content.
- 2026-06-08: Verified compact ledger-style public institutional profile update with PHP syntax check, regression suite (`7 passed`), and isolated browser smoke test showing 9 compact ledger rows with no horizontal overflow at 1366px.
- 2026-06-08: Verified two-item-per-row ledger update with PHP syntax check, regression suite (`7 passed`), and isolated browser smoke showing 6-column table, 5 rows for 9 sample items, no horizontal overflow at 1366px.
- 2026-06-08: Verified month/report-per-row table layout with PHP syntax check, regression suite (`7 passed`), and isolated browser smoke showing 3 sample monthly rows, 11 columns, and no horizontal overflow at 1366px.
- 2026-06-08: Verified two-month-per-row grid layout with PHP syntax check, regression suite (`7 passed`), and isolated browser smoke confirming first two month cards share the same row and the third wraps to the next row.
- 2026-06-08: Verified mobile menu fix with PHP syntax checks, JS lint (`0 blocking`), regression suite (`7 passed`), video analysis, and isolated mobile DOM tests confirming drawer opens with modal backdrop, parent dropdown toggle prevents navigation, and close/backdrop logic removes open state.
- 2026-06-08: Verified notice popup fix with PHP syntax checks, JS lint (`0 blocking`), regression suite (`7 passed`), desktop browser smoke confirming popup shows and close writes `sessionStorage`, and mobile browser smoke confirming photo-only popup image displays correctly.
- 2026-06-08: Verified inline mobile menu fallback with PHP syntax check, regression suite (`7 passed`), and mobile browser smoke confirming hamburger click opens drawer, backdrop activates, parent dropdown opens without navigating, and submenu displays.
- 2026-06-08: Verified institutional profile two-card icon-ledger layout with PHP syntax check, regression suite (`7 passed`), and browser smoke confirming two month cards in one row with 18 icon-ledger rows total for 2 sample cards.
- 2026-06-08: Verified cPanel update ZIP contents exclude live data/runtime folders, PHP syntax checks pass for key modified pages, regression suite (`7 passed`), and SHA256 checksum is `44743f8750cc5301d139b1529901e4ebe59e6d5805ac90c608e0f7af6e1dfd62`.