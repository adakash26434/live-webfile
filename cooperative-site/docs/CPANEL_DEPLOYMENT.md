## cPanel Git Deployment — Setup Guide

### समस्या के थियो?
GitHub repo मा सबै website files `cooperative-site/` subfolder भित्र छन्।
cPanel ले यसलाई pull गर्दा `public_html/cooperative-site/` मा land हुन्थ्यो — **गलत structure**।

### Fix गरिएको
`.cpanel.yml` file थपिएको छ repo root मा। cPanel ले automatically यो file run गर्छ र:
1. `cooperative-site/` contents → `public_html/` root मा copy गर्छ
2. Dev/test files (`tests/`, `scripts/`, `memory/`, `docs/`) exclude गर्छ
3. Unwanted platform files (`.emergent/`, etc.) cleanup गर्छ

---

### cPanel मा Setup Steps

#### पहिलो पटक:
1. cPanel → **Git Version Control** → Create
2. Repository URL: `https://github.com/yourusername/yourrepo`
3. Repository Path: `/home/yourusername/public_html` *(अथवा subdomain folder)*
4. Branch: `main`
5. **Create** गर्नुहोस्

#### हरपटक Deploy गर्दा:
1. Emergent Platform मा → **"Save to GitHub"** click गर्नुहोस्
2. cPanel → Git Version Control → **Pull or Deploy** button
3. `.cpanel.yml` automatically run हुन्छ → files ठीक structure मा deploy!

---

### Production मा जाने files (deploy हुने):
- `admin/`, `assets/`, `includes/`, `member/` — सबै website files
- `*.php` root files (index.php, about.php, etc.)
- `database/` (schema only — no credentials)

### Production मा नजाने files (excluded):
- `tests/` — regression test suite
- `scripts/` — dev Python scripts
- `memory/` — agent memory files
- `docs/` — internal documentation
- `archive_old_v1/` — archived deprecated files
- `*.zip` — cPanel update packages
- `.emergent/`, `.gitconfig` — platform metadata

---

### Database Credentials
⚠️ `includes/database.php` file production server मा अलग राख्नुहोस्।
Git repository मा `.env` र database credentials track हुँदैनन् (`.gitignore` मा छ)।
