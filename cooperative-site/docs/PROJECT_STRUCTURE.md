# Project Structure Map

> Detailed directory & file reference for the Aakash Cooperative web app.
> Updated: **2026-06-10**

---

## Top-level

```
/app
├── 📄 README.md                   ← project overview (start here)
├── 📄 README_CPANEL_UPDATE.txt    ← cPanel deploy notes (older release)
├── 🔧 _bootstrap.php              ← PHP boot file
├── 🌐 *.php (56 files)            ← public-facing pages
├── 🛠 admin/                       ← admin panel
├── 👤 member/                      ← member portal
├── 🌐 includes/                    ← shared PHP includes
├── 🔌 core/                        ← cross-cutting init
├── 🎨 assets/                      ← CSS / JS / images
├── 🗄 database/                    ← install.sql
├── 📤 public/                      ← static public files
├── 📚 docs/                        ← documentation (this folder)
├── 🗃 archive_old_v1/              ← quarantined legacy files
├── ✅ tests/                       ← pytest regression
└── 📝 memory/                      ← internal PRD log (agent notes)
```

---

## 🌐 Root public pages (entry points)

| File | Purpose |
|------|---------|
| `index.php` | Homepage with hero slider, services, news, digital-services widget |
| `about.php`, `cooperative-programs.php`, `committees.php`, `awards.php` | Static info pages |
| `services.php`, `loan.php`, `loan-apply.php`, `loan-eligibility.php` | Service catalogue + loan application |
| `online-kyc.php`, `online-account.php`, `online-account-status.php` | Public-facing KYC + account flows |
| `digital-services.php`, `emi-calculator.php`, `date-converter.php`, `exchange-rate.php`, `downloads.php` | Self-service tools |
| `notices.php`, `notice-detail.php`, `gallery.php`, `career.php`, `career-detail.php`, `faqs.php` | Content browsing |
| `contact.php`, `appointment.php`, `grievance.php`, `member-welfare.php` | Contact / forms |
| `member-of-year.php`, `auction.php`, `election-information.php`, `attend.php` | Featured public modules |
| `application-tracker.php`, `survey-public.php`, `vendor-enlistment.php` | Public-facing trackers |
| `404.php`, `500.php` | Error pages |
| `install.php` | First-run installer (DELETE after successful setup) |
| `api-public-chat.php`, `cron-cleanup.php`, `backup-restore.php` | Endpoints / utilities |

---

## 🛠 admin/ (admin panel — 90 files)

```
admin/
├── index.php                    Dashboard
├── login.php / logout.php       Admin auth
├── settings.php                 Global settings (branding, SMTP, etc.)
├── system-info.php              PHP/DB diagnostics
│
├── members.php                  Member list / search
├── members/                     Member sub-pages (KYC review, online-applications, profile, etc.)
│
├── pages.php                    📝 Page-content CMS (canonical, replaces old pages-v2.php)
├── notices.php                  Notice publisher
├── sliders.php                  Hero-slider manager
├── gallery.php                  Photo gallery admin
├── careers.php                  Job posts
├── faqs.php                     FAQ editor
├── awards.php                   Awards/recognitions
├── auction.php                  Auction posts
├── election.php                 Election info
│
├── hrm-dashboard.php            🧑‍💼 HRM module dashboard
├── hrm-employees.php            Employee master
├── hrm-departments.php          Departments / branches
├── hrm-messages.php             Internal messenger
│
├── institutional-profile.php    Annual financial profile
├── about-settings.php / why-choose.php / vision-mission.php
├── financial-reports.php / financials-detail.php
│
├── loans.php / loan-rates.php / interest-rates.php / saving-rates.php
├── digital-services.php / member-of-year.php / member-services.php
├── welfare-claims.php / vendor-enlistment.php / grievances.php
├── appointments.php / committees.php
│
├── account-applications.php / kyc-applications.php / loan-applications.php
├── application-status.php / approval-letters.php
│
├── notification-templates.php / push-notifications.php / sms-settings.php
├── audit-log.php / member-activities.php / app-features.php / useful-links.php
├── help-guide.php               Admin user guide (in-app)
│
├── _partials/
│   ├── header.php               Admin page-header partial
│   ├── footer.php               Admin page-footer + bottom-nav
│   ├── stat-cards.php           Reusable stat cards
│   └── print-form.php           Print layout
│
├── includes/
│   ├── admin-header.php         Admin shell (sidebar nav, top bar)
│   ├── admin-footer.php
│   ├── admin-ui.php             Reusable PHP UI helpers (adminPageHeader, adminAddBtn, …)
│   ├── admin-auth.php           Session / role guard
│   └── (more helpers)
│
├── api/
│   └── quick-search.php         Admin global quick-search AJAX
│
├── applications/                Form rendering helpers (account, KYC, member)
├── members/                     Member sub-pages
└── assets/
    ├── css/                     Admin-only icon styles / partial overrides
    └── icons/                   Admin icon SVGs
```

---

## 👤 member/ (member portal — 31 files)

```
member/
├── index.php                    Member dashboard
├── login.php / logout.php       Member auth
├── register.php                 Self-registration
├── digital-service.php          Digital services UI
├── welfare.php                  Welfare claim form
├── loan-application.php / account-application.php
├── kyc.php / kyc-print.php
├── notifications.php / messages.php
├── statement.php / loan-statement.php
├── profile.php / change-password.php
├── _partials/                   Header / footer / mini stat-cards
├── includes/
│   ├── chrome.php               Member shell (sidebar, top bar)
│   ├── chrome-foot.php          Mobile bottom-nav for member
│   └── member-auth.php
└── assets/                      Member-only icons / partial CSS
```

---

## 🌐 includes/ (shared PHP — 52 files)

```
includes/
├── config.php                   ⭐ Top-level config loader
├── compatibility.php            PHP version guard
├── database.dist.php            DB connection template (rename to .local.php on server)
├── database.local.php.example   Example local DB config
├── error-handler.php            Centralized error handler
├── credentials-crypto.php       AES-256 helpers for member credentials
├── auth-roles.php               Role/permission constants + helpers
├── member-auth.php              Member session helpers
├── member-generator.php         Member ID/code generator
├── nepal-address.php            Nepal address dataset + lookup helpers
├── nepali-bs-convert.php        BS ↔ AD calendar conversion
├── nepali-bs-month-days.generated.php
├── theme-assets.php             Helper to print CSS/JS in correct order
├── panel-uniform.php            Panel chrome helpers
├── nav-menu-badges.php          Top-nav badge counts
├── mobile-footer-nav.php        Public mobile bottom-nav
├── header.php / footer.php      Public chrome
├── ensure-tables.php            On-the-fly schema migrations
├── audit.php                    Audit-log writer
├── kyc-public-form.php / kyc-capture-helpers.php
├── member-prefill-block.php / member-widgets.php
├── components/                  Reusable HTML/PHP components
│   └── (notice cards, stat cards, etc.)
├── auction-tables.php / careers-tables.php / election-tables.php
│   / member-of-year-tables.php / member-partner-services-tables.php
│   / digital-service-requests-tables.php / notification-templates-tables.php
└── notification-templates.php   Template renderer
```

---

## 🎨 assets/

```
assets/
├── css/
│   ├── global-theme.php         ⭐ FINAL CANONICAL — loaded LAST on every page
│   ├── app-core.css             Universal foundation (.btn-coop, .card-coop, base type)
│   ├── app-public.css           Public-site styles (~23k lines)
│   ├── app-admin.css            Admin-panel styles (~16.9k lines, consolidated 2026-06-08)
│   ├── app-member.css           Member-portal styles
│   └── (1 more legacy partial)
├── js/
│   ├── coop-mobile.js           Mobile interaction helpers
│   ├── v9-mobile-fix.js         Mobile menu / drawer fixes
│   ├── main.js                  Public homepage interactions
│   ├── form-validation.js       Form validation library
│   ├── kyc-capture.js           Camera / file capture for KYC
│   ├── nepali.datepicker.min.js BS calendar picker
│   ├── search-improved.js       Public search UX
│   ├── pull-to-refresh.js       Mobile pull-to-refresh
│   ├── pwa-register.js          Service worker registration
│   └── scroll-accessibility.js  A11y scroll helpers
└── images/                      Logos, placeholders, favicons (logo.svg, *.jpg)
```

---

## 🗄 database/

```
database/
└── install.sql                  74 CREATE TABLE statements (consolidated 2026-06-08)
                                  — runs via /install.php on first deploy
```

Key tables:
- **Auth/users:** `admin_users`, `members`, `members_credentials`
- **Content:** `notices`, `sliders`, `pages`, `static_pages`, `gallery_*`, `faqs`, `awards`, `auctions`, `careers`
- **Member services:** `account_applications`, `kyc_applications`, `loan_applications`, `welfare_claims`
- **HRM:** `hrm_departments`, `hrm_employees`, `hrm_employee_contracts`, `hrm_employee_documents`, `hrm_employee_education`, `hrm_employee_experience`, `hrm_employee_family`, `hrm_employee_bank`, `hrm_employee_history`, `hrm_internal_messages`
- **Cross-cutting:** `audit_log`, `notifications`, `notification_templates`, `site_settings`, `site_stats`, `institutional_profile`

---

## 📚 docs/

```
docs/
├── PROJECT_STRUCTURE.md         (this file)
├── CSS_ARCHITECTURE.md          CSS cascade & override rules
└── REFACTOR_AUDIT_2026-06-10.md Audit findings + cleanup log
```

---

## 🗃 archive_old_v1/

```
archive_old_v1/
├── README.md                    How to archive / restore
└── MOVED.log                    Append-only log of moves
```

**Rule:** active app must run identically with this folder absent.

---

## ✅ tests/

```
tests/
├── test_php_feature_regression.py   14 pytest tests
├── php_include_regression.txt
└── php_lint_report.txt
```

Run: `python3 -m pytest tests/ -v`

---

## 📝 memory/ (internal — agent notes)

```
memory/
├── PRD.md                       Iteration / product history
└── test_credentials.md          Test user credentials (gitignored on server)
```

---

## File-count summary (2026-06-10)

| Layer | Count |
|-------|-------|
| Root PHP pages | 56 |
| `admin/` PHP | 90 |
| `member/` PHP | 31 |
| `includes/` PHP | 52 |
| `core/` PHP | 1 |
| **Total active PHP** | **270** |
| CSS files | 6 |
| JS files | 12 (last cleanup: removed 3 unreferenced) |
| MySQL tables in `install.sql` | 74 |
| Pytest regression tests | 14 (all passing) |
