# CSS Architecture & Cascade Strategy

> How CSS is loaded, layered, and overridden across the Aakash Cooperative codebase.
> Last refactor: **2026-06-08 / 2026-06-10**

---

## 1. Why this document exists

The project's CSS grew organically over many iterations. Multiple files contained
duplicate definitions of the same selectors (e.g., `.stat-uniform-card` was defined
**4 times**, `.nav-tabs` was defined **5+ times**, with `!important` chains fighting
each other). This led to:

- **CSS specificity wars** — fixes in one file got overridden by another
- **Visual regressions** — fixing a button in one panel broke it elsewhere
- **300+ lines of dead CSS** silently overridden by later rules

The 2026-06-08 refactor consolidated this into a **strict cascade order** with
a single canonical "final-pass" file that wins every conflict.

---

## 2. Cascade order (every page)

```
┌─────────────────────────────────────────────┐
│ 1. Bootstrap 5 (CDN, jsdelivr)              │  base framework
├─────────────────────────────────────────────┤
│ 2. Font Awesome 6 (CDN)                     │  icons
├─────────────────────────────────────────────┤
│ 3. /app/assets/css/app-core.css             │  universal foundation:
│                                              │  • CSS tokens (:root vars)
│                                              │  • .btn, .btn-coop, .card-coop
│                                              │  • base typography
├─────────────────────────────────────────────┤
│ 4. ONE OF (depending on panel):             │  panel-specific:
│      app-public.css                         │  • homepage hero/sliders
│      app-admin.css                          │  • admin shell + dashboards
│      app-member.css                         │  • member portal chrome
├─────────────────────────────────────────────┤
│ 5. /app/assets/css/global-theme.php  ⭐     │  FINAL — wins every conflict:
│                                              │  • brand tokens
│                                              │  • FINAL UNIFORMITY PATCH
│                                              │  • FIX-PASS 2 (2026-06-10)
│                                              │  • FIX-PASS 3 (2026-06-10)
└─────────────────────────────────────────────┘
```

The loader is `/app/includes/theme-assets.php` which prints `<link>` tags in this
exact order with `?v=<filemtime>` cache busting.

---

## 3. File responsibilities

### `assets/css/app-core.css` (~6,200 lines)
- CSS custom-property tokens (`--primary-color`, `--radius-md`, ...)
- Universal component classes: `.btn-coop`, `.card-coop`, `.btn`, `.divider-icon`
- Base typography, default form controls
- **Do not add panel-specific styles here.**

### `assets/css/app-public.css` (~23,100 lines)
- Public site (homepage, services, contact, etc.)
- Hero sliders, public notice ticker, mobile drawer base
- **Edit only when adding a new public-facing component.**

### `assets/css/app-admin.css` (~16,900 lines)
- Admin sidebar / dashboard chrome
- Admin tables, forms, modals
- Stat cards (canonical block near end of file)
- **Edit cautiously — consolidated 2026-06-08.**

### `assets/css/app-member.css` (~7,000 lines)
- Member portal sidebar / dashboard
- Member-specific stat tiles + form layouts

### `assets/css/global-theme.php` ⭐
**This file ALWAYS wins.** It's loaded last on every page.

Structure:
```php
<?php // generates tokens from DB settings (theme color, etc.) ?>
<style>
  :root { /* dynamic tokens injected by PHP */ }
  /* …existing brand utility classes… */

  /* FINAL UNIFORMITY PATCH (2026-06-08) */
  /*   - kills a.btn underline
       - fixes Devanagari descender clip
       - guarantees button icon contrast
       - inactive nav-tabs on green bg visible
       - .admin-bottom-nav icons opacity:1
       - .stat-uniform-card icon per data-bg */

  /* FIX-PASS 2 (2026-06-10) */
  /*   - public tools-category-card h5 contrast
       - .btn-coop Devanagari-safe padding + overflow:visible
       - institutional-profile button defensive padding */

  /* FIX-PASS 3 (2026-06-10) */
  /*   - global icon sizing (0.92em-0.95em)
       - Devanagari descender safety (height:auto, line-height:1.45)
       - badge inline-flex padding bump 4px→5px */
</style>
```

**Rule:** any new project-wide override goes inside this file, appended as a new
`/* FIX-PASS N */` block with a comment header.

---

## 4. Adding a new override — workflow

```text
Q: Where do I add a CSS fix for a button that looks wrong on /member/welfare.php?

  ✅ Is this a member-portal-only style?           → app-member.css
  ✅ Is this an admin-only style?                  → app-admin.css
  ✅ Is this a universal fix affecting all panels? → global-theme.php (final block)
  ✅ Is this a brand token update                  → global-theme.php (:root block)
       (e.g., new --primary-color)?
```

### Naming convention inside `global-theme.php`

Each new global override block must start with:
```css
/* ══════════════════════════════════════════════════════════════════════
   FIX-PASS N (YYYY-MM-DD) — short description of the issue solved
   - Bullet 1
   - Bullet 2
   ══════════════════════════════════════════════════════════════════════ */
```

This makes it easy to grep `FIX-PASS ` and see the project's CSS history.

---

## 5. Devanagari (Nepali script) safety rules — MANDATORY

The Devanagari script has descender modifiers (ँ ी ु ृ) that extend **below**
the baseline. Fixed-height containers crop them. Therefore:

| Element | ❌ Don't | ✅ Do |
|---------|---------|------|
| `.btn`, `.btn-coop`, `.btn-sm` | `height: 32px` | `min-height: 40px; padding-block: 9px` |
| `.badge`, `.chip`, `.pill` | `height: 18px` | `min-height: 22px; padding-block: 5px` |
| `.nav-link`, `.dropdown-item` | `height: Xpx` | `padding-block: 8px; line-height: 1.45` |
| Button text wrapper | `overflow: hidden` | `overflow: visible` |
| Anchor used as button | (default underline) | `text-decoration: none` |
| Icon inside button | (default `1em+` font-size) | `font-size: 0.92em-0.95em` |

FIX-PASS 3 enforces all of these globally via `!important` declarations targeting
common selectors. **If you ever see Devanagari text clipping again**, check that
you haven't introduced a new fixed `height` rule.

---

## 6. Icon sizing convention

| Context | Size cap |
|---------|---------|
| `.dropdown-menu .dropdown-item i` | `0.92em`, `width: 1.15em` |
| `.btn > i`, `.btn-coop > i` | `0.95em` |
| `.badge > i` | `0.88em` |
| `.nav-link > i`, `.sidebar-nav a > i` | `0.95em`, `width: 1.2em` |

Use `em` (relative) **not `px`** so icons scale with surrounding text.

---

## 7. Variable / token reference

Defined in `global-theme.php` `:root { }`:

| Token | Purpose | Default |
|-------|---------|---------|
| `--primary-color` | Brand green | `#1a5f2a` |
| `--primary-dark` | Hover / active green | `#144a21` |
| `--primary-rgb` | RGB triplet for rgba() | `26, 95, 42` |
| `--secondary-color` | Accent red | `#dc2626` |
| `--text-on-primary` | Text contrast on primary bg | `#ffffff` |
| `--text-primary` | Body text | `#1f2937` |
| `--text-secondary` | Muted text | `#4b5563` |
| `--text-muted` | Subtle | `#6b7280` |
| `--bg-card` | Card background | `#ffffff` |
| `--bg-soft` | Section background | `#f5faf6` |
| `--border-color` | Standard border | `#e5e7eb` |
| `--radius-sm` / `--radius-md` / `--radius-lg` | Corner radii | `6 / 10 / 16 px` |
| `--shadow-sm` / `--shadow-lg` | Drop shadows | tuned |
| `--font-primary` | Body font | Mukta / Noto Sans Devanagari |

Override these in `global-theme.php` (top `:root` block) to retheme the whole app
without touching any other file.

---

## 8. Audits

Run before any major CSS edit:

```bash
# CSS brace balance check
for f in assets/css/*.css; do
  o=$(grep -c '{' "$f"); c=$(grep -c '}' "$f")
  echo "$f: open=$o close=$c diff=$((o-c))"
done

# Count duplicate selector definitions
grep -nE "^\.btn-coop\s*\{" assets/css/app-core.css | wc -l   # should be ≤2
grep -nE "^\.stat-uniform-card\s*\{" assets/css/app-admin.css | wc -l  # should be 1
grep -nE "^\.nav-tabs\s*\{" assets/css/app-admin.css | wc -l   # should be ≤2
```

Regression test (Python):
```bash
python3 -m pytest tests/test_php_feature_regression.py -v
```

---

— Last updated: **2026-06-10**
