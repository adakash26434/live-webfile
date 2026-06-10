# Aakash Cooperative — Web Application

A complete digital platform for **Aakash Cooperative (आकाश सहकारी)** built in **pure PHP 8.0+ / MySQL** with a Bootstrap-based responsive UI. The system serves the **public website**, an **admin panel** for staff, and a **member portal** with online KYC, loan/account applications, welfare claims, an HRM module, and more — all wired to a single MySQL database via the cPanel hosting environment.

---

## 1. Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP **8.0+** (8.2 recommended) |
| Database | MySQL / MariaDB (UTF-8 / `utf8mb4_unicode_ci`) |
| Frontend | Bootstrap 5 + custom CSS (`assets/css/*`) + Font Awesome 6 |
| JS | Vanilla JS modules (`assets/js/*`) — no React/Vue |
| Hosting | cPanel shared hosting (Apache / Nginx + PHP-FPM) |
| Fonts | Mukta / Noto Sans Devanagari (Devanagari-safe) |
| Build | **None.** PHP serves CSS via `assets/css/global-theme.php` with cache-busting via `filemtime` |

---

## 2. High-Level Project Map

```
/app
├── _bootstrap.php               # PHP bootloader (sessions, encoding, includes)
├── index.php                    # Public homepage
├── *.php                        # Public pages (about, services, contact, etc.) — 56 entry points
├── 404.php / 500.php            # Error pages
├── install.php                  # First-run installer (runs database/install.sql)
│
├── admin/                       # 🛠 Admin panel (90 PHP entry-points)
│   ├── index.php                # Admin dashboard
│   ├── login.php / logout.php   # Admin auth
│   ├── pages.php                # Page-content CMS (canonical — replaces old pages-v2.php)
│   ├── hrm-dashboard.php        # HRM module
│   ├── hrm-employees.php
│   ├── institutional-profile.php
│   ├── members/                 # member-* sub-pages
│   ├── applications/            # account / KYC / loan applications
│   ├── api/                     # admin AJAX endpoints
│   ├── _partials/               # Shared partials (header/footer/stat-cards/print-form)
│   ├── includes/                # admin-only includes (admin-header.php, admin-ui.php, ...)
│   └── assets/                  # admin-specific assets (icons, partial CSS)
│
├── member/                      # 👤 Member portal (31 PHP entry-points)
│   ├── index.php                # Member dashboard
│   ├── login.php / logout.php
│   ├── digital-service.php
│   ├── welfare.php
│   ├── _partials/               # Shared member partials
│   ├── includes/                # Member chrome (chrome.php, chrome-foot.php)
│   └── assets/                  # Member-specific assets
│
├── includes/                    # 🌐 Shared PHP includes (52 files)
│   ├── config.php               # Global config loader
│   ├── database.dist.php        # DB connection template (cPanel rename to .local.php)
│   ├── compatibility.php        # PHP version guard
│   ├── header.php / footer.php  # Public chrome
│   ├── mobile-footer-nav.php    # Public bottom nav (mobile)
│   ├── auth-roles.php           # Role/permission helpers
│   ├── nepal-address.php        # Nepal address (provinces/districts/munis)
│   ├── nepali-bs-convert.php    # BS ↔ AD calendar
│   ├── components/              # Reusable HTML/PHP components
│   └── ensure-tables.php        # On-the-fly schema migrations
│
├── core/                        # 🔌 Cross-cutting core helpers
│   └── init.php                 # Re-exported by _bootstrap.php
│
├── assets/                      # 🎨 Static assets
│   ├── css/
│   │   ├── global-theme.php     # ⭐ CANONICAL theme — loaded LAST on every page
│   │   ├── app-core.css         # Universal (`.btn-coop`, `.card-coop`, base typography)
│   │   ├── app-public.css       # Public-site styles
│   │   ├── app-admin.css        # Admin-panel styles
│   │   └── app-member.css       # Member-portal styles
│   ├── js/                      # 12 JS files (form-validation, kyc-capture, datepicker, etc.)
│   └── images/                  # Logos, placeholders, favicons
│
├── database/
│   └── install.sql              # 74 CREATE TABLE statements — fresh-install schema
│
├── public/                      # Public-facing static files (sitemap, robots.txt, etc.)
├── docs/                        # 📚 Project documentation (this folder)
│   ├── PROJECT_STRUCTURE.md     # Detailed directory map
│   ├── CSS_ARCHITECTURE.md      # CSS cascade & override strategy
│   └── REFACTOR_AUDIT_2026-06-10.md
│
├── archive_old_v1/              # 🗄 Quarantined legacy files (see its README)
├── tests/                       # ✅ pytest regression tests (PHP syntax, CSS rules, address data)
└── memory/                      # 📝 PRD.md (internal product history — agent notes)
```

---

## 3. CSS Architecture (CRITICAL)

The CSS cascade is **explicitly ordered** so panel-specific rules win over base
rules, and a single FINAL block in `global-theme.php` wins over everything.

```
Load order (every page):
┌─────────────────────────────────────────┐
│  1. Bootstrap 5 (CDN)                   │
│  2. Font Awesome 6 (CDN)                │
│  3. assets/css/app-core.css             │  ← universal foundation
│  4. assets/css/app-public.css           │
│     OR assets/css/app-admin.css         │  ← panel-specific (one of these)
│     OR assets/css/app-member.css        │
│  5. assets/css/global-theme.php  ⭐     │  ← FINAL — beats everything
└─────────────────────────────────────────┘
```

### `assets/css/global-theme.php`

Holds the **FINAL UNIFORMITY PATCH** + 3 fix-passes (consolidated 2026-06-10):

| Block | Purpose |
|-------|---------|
| Core theme tokens (`:root` vars) | Brand colors, spacing, radius, shadows |
| FINAL UNIFORMITY PATCH | `a.btn` underline kill, button overflow fix, nav-tabs visibility, stat-card icon contrast, sidebar icon visibility |
| FIX-PASS 2 (2026-06-10) | Public `डिजिटल सेवाहरू` h5 contrast, `.btn-coop` Devanagari clip fix, institutional-profile button padding |
| FIX-PASS 3 (2026-06-10) | Global icon sizing (`0.92em-0.95em`), Devanagari descender safety (`.btn / .badge / .chip` flex padding) |

**Rule:** any new theme override must be appended **inside** `global-theme.php`'s final block — not added to `app-admin.css` / `app-public.css` where it will be overridden.

📖 Full detail in `docs/CSS_ARCHITECTURE.md`.

---

## 4. Setup (cPanel deployment)

1. **Upload** the entire `/app/*` directory to your cPanel `public_html`.
2. **Database**:
   - Create a MySQL database + user via cPanel "MySQL Databases".
   - Copy `includes/database.dist.php` → `includes/database.local.php` and fill credentials.
   - Open `https://yourdomain/install.php` once — it will run `database/install.sql`.
   - **Delete** `install.php` after first successful run.
3. **PHP Version**: cPanel → Software → "Select PHP Version" → set to **PHP 8.2** (8.0 minimum).
4. **First admin login**: see `install.php` output for seeded credentials. Change them immediately from `/admin/settings.php`.
5. **PWA / Service Worker**: served from `/sw.js` at site root — no extra config.

---

## 5. Development Conventions

### File naming
- Use **kebab-case** for new PHP files: `member-welfare.php`, not `memberWelfare.php`.
- Place shared helpers in `/app/includes/`.
- Place admin-only helpers in `/app/admin/includes/`.

### Never break the cascade
- Do **not** edit `app-admin.css` / `app-public.css` ad-hoc — they contain consolidated styles.
- Append theme tweaks to the **FINAL block of `global-theme.php`** with a comment header.

### Devanagari-safe component rules (FIX-PASS 3)
- Never set fixed `height: Xpx` on buttons/badges/chips — use `min-height` + `padding-block`.
- Icon size inside dropdowns/buttons MUST be capped at `0.92em-0.95em` (already enforced globally).

### Deprecating a file
1. Verify zero active references (see `archive_old_v1/README.md`).
2. Move (don't delete) to `archive_old_v1/<same-relative-path>`.
3. Append a line in `archive_old_v1/MOVED.log`.
4. Run `python3 -m pytest tests/test_php_feature_regression.py` to confirm nothing breaks.

---

## 6. Testing

```bash
# Full regression suite (PHP syntax + CSS sanity + DB schema checks)
python3 -m pytest tests/test_php_feature_regression.py -v

# PHP lint every file
find . -name "*.php" -not -path "./.git/*" -not -path "./archive_old_v1/*" \
    -print0 | xargs -0 -n1 -P4 php -l | grep -v "No syntax errors"
```

Current status: **270 PHP files lint-clean • 14/14 regression tests pass • 6 CSS files brace-balanced**.

---

## 7. Production Cache Busting

CSS is served via `assets/css/global-theme.php?v=<filemtime>`. After deploying
new CSS, **no manual cache action needed** — `filemtime` auto-changes. Browser
hard-refresh (Ctrl+Shift+R) only needed for users with aggressive local cache.

---

## 8. Project History

See `memory/PRD.md` (internal product / iteration log) and `docs/REFACTOR_AUDIT_2026-06-10.md`.

---

## 9. License & Ownership

Internal property of Aakash Cooperative. Not for redistribution.

— Last updated: **2026-06-10**
