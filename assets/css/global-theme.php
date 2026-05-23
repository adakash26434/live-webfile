<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * 🎨 GLOBAL THEME — Dynamic CSS Variable Injector
 * ═══════════════════════════════════════════════════════════════
 * फाइल: assets/css/global-theme.php
 *
 * यो एउटै फाइलले Public Website, Admin Portal, Member Portal र
 * Verify Portal — सबैको color theme handle गर्छ।
 *
 * 📌 प्रयोग गर्ने तरिका — includes/theme-assets.php → coopThemeHeadAssets()
 *   (यो file स्वतः design-tokens पछि load हुन्छ; सिधै require नगर्नुहोस्)
 *
 * ✅ यसले admin/Settings बाट बदलिएको color तुरुन्त सबैतिर reflect गर्छ।
 * ✅ कतै पनि color hardcode नगर्नुस् — सधैं CSS variable प्रयोग गर्नुस्।
 * ═══════════════════════════════════════════════════════════════
 */

if (!function_exists('getSetting')) {
    return; // config.php include नभई यो file load नगर्नुस्
}

define('THEME_VERSION', '2.0');

/* ─── Hex normalizer ─── */
$__hex = function (string $raw, string $fallback = '#1a5f2a'): string {
    $v = trim($raw);
    if ($v === '') return strtolower($fallback);
    if (preg_match('/^#([0-9a-fA-F]{3})$/', $v, $m)) {
        $h = strtolower($m[1]);
        return '#' . $h[0].$h[0] . $h[1].$h[1] . $h[2].$h[2];
    }
    if (preg_match('/^#([0-9a-fA-F]{6})$/', $v)) {
        return strtolower($v);
    }
    return strtolower($fallback);
};

/* ─── Darken/Lighten helper ─── */
$__shift = function (string $hex, int $amt): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return '#' . $hex;
    $clamp = fn($v) => max(0, min(255, (int)$v));
    $r = $clamp(hexdec(substr($hex, 0, 2)) - $amt);
    $g = $clamp(hexdec(substr($hex, 2, 2)) - $amt);
    $b = $clamp(hexdec(substr($hex, 4, 2)) - $amt);
    return sprintf('#%02x%02x%02x', $r, $g, $b);
};

/* ─── rgba() helper ─── */
$__rgba = function (string $hex, float $alpha): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return "rgba(26,95,42,{$alpha})";
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "rgba({$r},{$g},{$b},{$alpha})";
};

/* ─── Best foreground text for a background ─── */
/* WCAG 2.1 relative luminance threshold: 0.179 (midpoint between white=1 and black=0) */
$__textOn = function (string $hex) use ($__rgba): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return '#ffffff';
    $toLinear = fn($c) => ($c <= 0.03928) ? ($c / 12.92) : pow(($c + 0.055) / 1.055, 2.4);
    $r = $toLinear(hexdec(substr($hex, 0, 2)) / 255);
    $g = $toLinear(hexdec(substr($hex, 2, 2)) / 255);
    $b = $toLinear(hexdec(substr($hex, 4, 2)) / 255);
    $lum = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    // WCAG: contrast ratio white vs bg vs black vs bg — pick higher contrast
    $contrastWhite = (1.05) / ($lum + 0.05);
    $contrastBlack = ($lum + 0.05) / (0.05);
    return ($contrastBlack > $contrastWhite) ? '#111827' : '#ffffff';
};

/* ─── RGB components ─── */
$__rgb = function (string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return '26, 95, 42';
    return hexdec(substr($hex, 0, 2)) . ', '
         . hexdec(substr($hex, 2, 2)) . ', '
         . hexdec(substr($hex, 4, 2));
};

/* ═══════════════════════════════════════════════════════════
   DB बाट colors पढ्ने
   ═══════════════════════════════════════════════════════════ */
$_p  = $__hex((string) getSetting('primary_color',   '#1a5f2a'), '#1a5f2a'); // Primary brand
$_s  = $__hex((string) getSetting('secondary_color', '#c0392b'), '#c0392b'); // Accent/secondary
$_h  = $__hex((string) getSetting('header_color',    $_s),       $_s);       // Header/topbar
$_f  = $__hex((string) getSetting('footer_color',    $_p),       $_p);       // Footer

/* Shades */
$_pDark   = $__shift($_p, 36);
$_pLight  = $__shift($_p, -28);
$_pXLight = $__shift($_p, -48);
$_sDark   = $__shift($_s, 30);
$_hDark   = $__shift($_h, 30);
$_fDark   = $__shift($_f, 24);

/* Foreground text colors */
$_onP = $__textOn($_p);
$_onS = $__textOn($_s);
$_onH = $__textOn($_h);
$_onF = $__textOn($_f);

/* RGB for rgba() usage */
$_pRgb = $__rgb($_p);
$_sRgb = $__rgb($_s);

/* Shadows */
$_shadowP  = "0 4px 20px " . $__rgba($_p, 0.20);
$_shadowS  = "0 4px 16px " . $__rgba($_s, 0.20);
$_shadowFocus = "0 0 0 3px " . $__rgba($_p, 0.18);

/* ─── Current panel detection ─── */
$_panel = 'public';
if (defined('IS_ADMIN_PAGE') && IS_ADMIN_PAGE) {
    $_panel = 'admin';
} elseif (!empty($_SERVER['PHP_SELF']) && str_contains((string)$_SERVER['PHP_SELF'], '/member/')) {
    $_panel = 'member';
} elseif (!empty($_SERVER['PHP_SELF']) && str_contains((string)$_SERVER['PHP_SELF'], '/verify')) {
    $_panel = 'verify';
}
?>
<style id="coop-global-theme" data-panel="<?= htmlspecialchars($_panel, ENT_QUOTES) ?>">
/* ═══════════════════════════════════════════════════════════
   🎨 GLOBAL THEME — Admin बाट DB मा save गरिएका रङहरू
   Portal: <?= $_panel ?> | Version: <?= THEME_VERSION ?>
   ═══════════════════════════════════════════════════════════ */
:root {
    /* ── Brand Colors ── */
    --primary-color:    <?= $_p ?>;
    --primary-dark:     <?= $_pDark ?>;
    --primary-light:    <?= $_pLight ?>;
    --primary-xlight:   <?= $_pXLight ?>;
    --primary-rgb:      <?= $_pRgb ?>;

    --secondary-color:  <?= $_s ?>;
    --secondary-dark:   <?= $_sDark ?>;
    --secondary-rgb:    <?= $_sRgb ?>;

    --header-color:     <?= $_h ?>;
    --header-dark:      <?= $_hDark ?>;
    --topbar-bg:        <?= $_h ?>;

    --footer-color:     <?= $_f ?>;
    --footer-dark:      <?= $_fDark ?>;

    /* ── Text on brand backgrounds ── */
    --text-on-primary:   <?= $_onP ?>;
    --text-on-secondary: <?= $_onS ?>;
    --text-on-header:    <?= $_onH ?>;
    --text-on-footer:    <?= $_onF ?>;

    /* ── Shadows derived from brand ── */
    --shadow-primary:  <?= $_shadowP ?>;
    --shadow-secondary:<?= $_shadowS ?>;
    --shadow-focus:    <?= $_shadowFocus ?>;

    /* ── Semantic surface colors (light mode defaults) ── */
    --bg-page:         #f8faf9;
    --bg-card:         #ffffff;
    --bg-soft:         #f5faf6;
    --bg-muted:        #e8f5e9;
    --bg-hover:        rgba(<?= $_pRgb ?>, 0.04);

    /* ── Text scale ── */
    --text-primary:    #1a2e1f;
    --text-secondary:  #4a5a4f;
    --text-muted:      #6b7280;
    --text-light:      #9ca3af;

    /* ── Borders ── */
    --border-color:    #e5e7eb;
    --border-soft:     #f0f0f0;
    --border-focus:    <?= $_p ?>;

    /* ── Status Colors (fixed, not brand-dependent) ── */
    --color-success:   #16a34a;
    --color-warning:   #d97706;
    --color-danger:    #dc2626;
    --color-info:      #0891b2;

    --color-success-bg: #f0fdf4;
    --color-warning-bg: #fffbeb;
    --color-danger-bg:  #fef2f2;
    --color-info-bg:    #ecfeff;

    --color-success-border: #bbf7d0;
    --color-warning-border: #fde68a;
    --color-danger-border:  #fecaca;
    --color-info-border:    #a5f3fc;

    /* ── Typography ── */
    --font-primary:    'Mukta', 'Noto Sans Devanagari', 'Inter', 'Segoe UI', sans-serif;
    --font-nepali:     'Noto Sans Devanagari', 'Mukta', sans-serif;
    --font-english:    'Inter', 'Poppins', 'Segoe UI', sans-serif;
    --font-mono:       'JetBrains Mono', 'Fira Code', monospace;

    --font-size-xs:    0.75rem;
    --font-size-sm:    0.8125rem;
    --font-size-base:  0.9375rem;
    --font-size-md:    1rem;
    --font-size-lg:    1.125rem;
    --font-size-xl:    1.25rem;
    --font-size-2xl:   1.5rem;
    --font-size-3xl:   1.875rem;

    /* ── Spacing scale ── */
    --space-xs:  4px;
    --space-sm:  8px;
    --space-md:  16px;
    --space-lg:  24px;
    --space-xl:  40px;
    --space-2xl: 64px;

    /* ── Border radius scale ── */
    --radius-sm:  6px;
    --radius-md:  10px;
    --radius-lg:  16px;
    --radius-xl:  24px;
    --radius-full: 9999px;

    /* ── Shadow scale ── */
    --shadow-xs:  0 1px 2px rgba(0,0,0,0.06);
    --shadow-sm:  0 1px 4px rgba(0,0,0,0.08);
    --shadow-md:  0 4px 16px rgba(0,0,0,0.10);
    --shadow-lg:  0 8px 32px rgba(0,0,0,0.12);
    --shadow-xl:  0 16px 48px rgba(0,0,0,0.14);

    /* ── Layout ── */
    --container-max:   1280px;
    --container-pad:   20px;

    /* ── Animation ── */
    --transition-fast:   0.15s ease;
    --transition-base:   0.25s ease;
    --transition-slow:   0.4s ease;

    /* ── Z-index scale ── */
    --z-dropdown:  100;
    --z-sticky:    200;
    --z-overlay:   300;
    --z-modal:     400;
    --z-toast:     500;
    --z-tooltip:   600;
}

/* ─── Bootstrap overrides: सबै panel मा consistent ─── */
.btn-primary, .bg-primary                    { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; color: var(--text-on-primary) !important; }
.btn-primary:hover, .btn-primary:focus       { background-color: var(--primary-dark) !important; border-color: var(--primary-dark) !important; color: var(--text-on-primary) !important; }
.btn-outline-primary                         { color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
.btn-outline-primary:hover                   { background-color: var(--primary-color) !important; color: var(--text-on-primary) !important; }
.text-primary                                { color: var(--primary-color) !important; }
.border-primary                              { border-color: var(--primary-color) !important; }

.form-control:focus, .form-select:focus      { border-color: var(--primary-color) !important; box-shadow: var(--shadow-focus) !important; }
.form-check-input:checked                    { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
.form-check-input:focus                      { box-shadow: var(--shadow-focus) !important; }

.nav-pills .nav-link.active,
.nav-tabs .nav-link.active                   { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; color: var(--text-on-primary) !important; }
.page-item.active .page-link                 { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; color: var(--text-on-primary) !important; }
.list-group-item.active                      { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
.progress-bar                                { background-color: var(--primary-color) !important; }

/* Header / Topbar */
.top-bar, .topbar, .site-topbar, .header-top, .navbar-top { background-color: var(--header-color) !important; color: var(--text-on-header) !important; }
.top-bar a, .topbar a, .site-topbar a       { color: var(--text-on-header) !important; }

/* Footer */
footer, .site-footer, .footer-main, .main-footer { background-color: var(--footer-color) !important; color: var(--text-on-footer) !important; }
footer a, .site-footer a, .main-footer a:not(.btn) {
    color: color-mix(in srgb, var(--text-on-footer) 88%, transparent);
}
footer a:hover, .site-footer a:hover, .main-footer a:not(.btn):hover {
    color: var(--text-on-footer);
}

/* Sidebar active state (admin/member) */
.sidebar-nav li.active > a,
.sidebar-nav a.active,
.mem-sidebar .active > a,
.admin-sidebar .active                      { color: var(--text-on-primary) !important; background: var(--primary-color) !important; }

/* ─── Auto text-contrast on brand-coloured backgrounds ─────────────────
   Rule: जुनसुकै element मा primary/secondary/header/footer रङ्को background
   आउँदा text colour automatically readable हुनुपर्छ।
   NOTE: global-theme.php ले --text-on-* variables WCAG luminance बाट
   compute गर्छ (light bg → dark text, dark bg → white text).
   ─────────────────────────────────────────────────────────────────────── */

/* Admin table headers using primary brand colour */
.admin-table thead th,
table.table-primary thead th,
.table > thead.bg-primary > tr > th,
.table > thead > tr.bg-primary > th        { background: var(--primary-color) !important;
                                             color: var(--text-on-primary) !important; }

/* Page banners & hero sections */
.page-banner, .page-banner-modern,
.page-banner h1, .page-banner h2, .page-banner h3,
.page-banner-modern h1, .page-banner-modern h2,
.page-banner .breadcrumb-item,
.page-banner .breadcrumb-item a,
.page-banner-modern .breadcrumb-item,
.page-banner-modern .breadcrumb-item a     { color: var(--text-on-primary) !important; }

/* Admin topbar — सेतो/हल्का bar; header strip (--header-color) होइन */
.admin-header, .admin-topbar {
    color: var(--text-primary) !important;
}
.admin-header .page-title,
.admin-header a:not(.btn):not(.admin-menu a),
.admin-topbar a:not(.btn) {
    color: var(--text-primary) !important;
}

/* Sidebar header brand area */
.sidebar .sidebar-header,
.sidebar .sidebar-brand-text               { color: var(--text-on-primary) !important; }

/* Coop-style buttons */
.btn-coop                                  { background: var(--primary-color) !important;
                                             border-color: var(--primary-color) !important;
                                             color: var(--text-on-primary) !important; }
.btn-coop:hover, .btn-coop:focus           { background: var(--primary-dark) !important;
                                             border-color: var(--primary-dark) !important;
                                             color: var(--text-on-primary) !important; }

/* Card headers that use primary/secondary backgrounds */
.card-header.bg-primary,
.card-header.bg-secondary                  { color: var(--text-on-primary) !important; }
.card-header.bg-secondary                  { background: var(--secondary-color) !important;
                                             color: var(--text-on-secondary) !important; }

/* Badges & pills */
.badge.bg-primary, .badge-primary          { color: var(--text-on-primary) !important; }
.badge.bg-secondary, .badge-secondary      { color: var(--text-on-secondary) !important; }

/* stat-card — white background, so use dark text (not --text-on-primary which is for brand-bg) */
/* Only stat-card variants with explicit brand background get light text */
.stat-card { color: var(--text-primary) !important; }
.stat-card h3, .stat-card .stat-number { color: var(--text-primary) !important; }
.stat-card p, .stat-card .stat-label    { color: var(--text-muted) !important; }
/* Brand-background variant: add class .stat-card-brand for colored cards */
.stat-card.stat-card-brand,
.stat-card.stat-card-brand h3,
.stat-card.stat-card-brand p,
.stat-card.stat-card-brand .stat-number,
.stat-card.stat-card-brand .stat-label  { color: var(--text-on-primary) !important; }

/* ══════════════════════════════════════════════════════════════════
   HERO SECTION — Text always white (overlay ensures dark background)
   app-public.css को color:#fff override हुन सक्छ; यहाँ directly fix
   ══════════════════════════════════════════════════════════════════ */
.hero-title-modern,
.slider-content .hero-title-modern,
.hero-content-modern .hero-title-modern,
.hero-text-wrapper h1                  {
    color: #fff !important;
    text-shadow: 0 2px 10px rgba(0,0,0,.32) !important;
}
.hero-subtitle-modern,
.slider-content .hero-subtitle-modern,
.hero-content-modern .hero-subtitle-modern,
.hero-text-wrapper p                   {
    color: rgba(255,255,255,.93) !important;
    text-shadow: 0 1px 6px rgba(0,0,0,.22) !important;
}
.hero-text-wrapper, .hero-content-modern {
    color: #fff !important;
}
.hero-btn-modern                       {
    background: var(--accent-color, var(--secondary-color)) !important;
    color: #fff !important;
    border-color: transparent !important;
}
.hero-btn-modern:hover                 {
    filter: brightness(1.12) !important;
    transform: translateY(-2px) !important;
}
/* Auction hero text */
.auc2-h, .auc2-sub                    {
    color: var(--text-on-primary,#fff) !important;
    text-shadow: 0 2px 8px rgba(0,0,0,.25) !important;
}

/* ══════════════════════════════════════════════════════════════════
   AUTH-PORTAL-PAGE — Login/Verify pages background (no pink tinge)
   ══════════════════════════════════════════════════════════════════ */
body.auth-portal-page                  {
    background: linear-gradient(160deg,
        color-mix(in srgb, var(--primary-color) 7%, var(--bg-page)),
        var(--bg-page) 55%,
        color-mix(in srgb, var(--primary-color) 4%, var(--bg-page))
    ) !important;
}

/* ══════════════════════════════════════════════════════════════════
   PANEL UNIFORM — Public / Member / Admin सबैमा एकैनाश CSS
   ══════════════════════════════════════════════════════════════════ */

/* ── Alert messages ── */
.alert-error, .alert-danger, .coop-alert-error  {
    background: var(--color-danger-bg,#fef2f2) !important;
    border-color: var(--color-danger-border,#fecaca) !important;
    color: var(--color-danger,#b91c1c) !important;
}
.alert-success, .coop-alert-success    {
    background: var(--color-success-bg,#f0fdf4) !important;
    border-color: var(--color-success-border,#bbf7d0) !important;
    color: var(--color-success,#15803d) !important;
}
.alert-warning, .coop-alert-warning    {
    background: var(--color-warning-bg,#fffbeb) !important;
    border-color: var(--color-warning-border,#fde68a) !important;
    color: var(--color-warning,#92400e) !important;
}
.alert-info, .coop-alert-info          {
    background: var(--color-info-bg,#eff6ff) !important;
    border-color: var(--color-info-border,#bfdbfe) !important;
    color: var(--color-info,#1e40af) !important;
}
.alert-error i, .alert-danger i        { color: var(--color-danger,#b91c1c) !important; }
.alert-success i, .coop-alert-success i{ color: var(--color-success,#15803d) !important; }
.alert-warning i                       { color: var(--color-warning,#92400e) !important; }
.alert-info i                          { color: var(--color-info,#1e40af) !important; }

/* ── Form fields ── */
.form-control, .form-select, .form-control-sm {
    border-color: var(--border-color) !important;
    background: var(--bg-card) !important;
    color: var(--text-primary) !important;
}
.form-control::placeholder             { color: var(--text-light) !important; }
.form-label, label.form-label          { color: var(--text-secondary) !important; }
.form-text                             { color: var(--text-muted) !important; }
.input-group-text                      {
    background: var(--bg-soft) !important;
    border-color: var(--border-color) !important;
    color: var(--text-secondary) !important;
}

/* ── Cards ── */
.card                                  { border-color: var(--border-color) !important; }
.card-title                            { color: var(--text-primary) !important; }
.card-text                             { color: var(--text-secondary); }
.card-footer                           {
    border-color: var(--border-soft) !important;
    background: var(--bg-soft) !important;
}
.card-header:not([class*="bg-"])       {
    background: var(--bg-soft) !important;
    border-color: var(--border-soft) !important;
    color: var(--text-primary) !important;
}

/* ── Tables ── */
.table                                 { color: var(--text-primary); }
.table th                              { color: var(--text-secondary) !important; font-weight: 600; }
.table-hover tbody tr:hover td         {
    background-color: var(--bg-hover) !important;
    color: var(--text-primary) !important;
}
.table > thead                         { background: var(--bg-soft); }
.table-bordered, .table-bordered td,
.table-bordered th                     { border-color: var(--border-soft) !important; }

/* ── Status badges ── */
.status-active, .badge-status-active   {
    background: var(--color-success-bg) !important;
    color: var(--color-success) !important;
    border: 1px solid var(--color-success-border) !important;
}
.status-pending, .badge-status-pending {
    background: var(--color-warning-bg) !important;
    color: var(--color-warning) !important;
    border: 1px solid var(--color-warning-border) !important;
}
.status-inactive, .status-rejected     {
    background: var(--color-danger-bg) !important;
    color: var(--color-danger) !important;
    border: 1px solid var(--color-danger-border) !important;
}

/* ── Text helpers ── */
.text-muted                            { color: var(--text-muted) !important; }
small, .small                          { color: var(--text-muted); }

/* ── OAuth / Social buttons ── */
.oauth-btn                             {
    border-color: var(--border-color) !important;
    background: var(--bg-soft) !important;
    color: var(--text-primary) !important;
}
.oauth-btn:hover                       {
    background: var(--bg-card) !important;
    border-color: var(--primary-color) !important;
}
.oauth-divider                         { color: var(--text-muted) !important; }
.oauth-divider::before,
.oauth-divider::after                  { background: var(--border-color) !important; }

/* ── Password rules ── */
.pw-rules                              { color: var(--text-muted); }
.pw-rules li.rule-ok                   { color: var(--color-success) !important; }
.pw-rules li.rule-muted                { color: var(--text-light) !important; }

/* ── Empty states ── */
.empty-state, .empty-msg, .no-data     { color: var(--text-muted) !important; }
.empty-state i, .empty-msg i,
.no-data i                             { color: var(--text-light) !important; }

/* ── Footer links ── */
.foot-link                             { color: var(--text-muted) !important; }
.foot-link a                           { color: var(--primary-color) !important; }
.foot-link a:hover                     { color: var(--primary-dark) !important; }

/* ── Dropdowns ── */
.dropdown-menu                         {
    border-color: var(--border-color) !important;
    background: var(--bg-card) !important;
    box-shadow: var(--shadow-md) !important;
}
.dropdown-item                         { color: var(--text-primary) !important; }
.dropdown-item:hover, .dropdown-item:focus {
    background: var(--bg-hover) !important;
    color: var(--primary-color) !important;
}
.dropdown-item.active, .dropdown-item:active {
    background: var(--primary-color) !important;
    color: var(--text-on-primary) !important;
}
.dropdown-divider                      { border-color: var(--border-soft) !important; }

/* ── Modals ── */
.modal-header                          { border-color: var(--border-soft) !important; }
.modal-footer                          {
    border-color: var(--border-soft) !important;
    background: var(--bg-soft) !important;
}
.modal-title                           { color: var(--text-primary) !important; }

/* ── Pagination ── */
.page-link                             {
    border-color: var(--border-color) !important;
    color: var(--primary-color) !important;
}
.page-link:hover                       {
    background: var(--bg-hover) !important;
    border-color: var(--primary-color) !important;
}
.page-item.disabled .page-link         {
    color: var(--text-light) !important;
    background: var(--bg-soft) !important;
}

/* ── Verify page logo ── */
.vp-page-logo img                      {
    max-height: 64px !important;
    max-width: 180px !important;
    width: auto !important;
    height: auto !important;
    object-fit: contain !important;
}
body.auth-portal-page .vp-page-logo img,
body.verify-page .vp-page-logo img     {
    max-height: 72px !important;
    max-width: 200px !important;
}

/* ══════════════════════════════════════════════════════════════════════
   AUTO-CONTRAST MASTER BLOCK
   ──────────────────────────────────────────────────────────────────────
   global-theme.php अब सबै panel CSS पछि load हुन्छ (theme-assets.php fix)
   त्यसैले यी rules ले app-public.css / app-admin.css / app-member.css
   का कुनै पनि same-specificity color declaration लाई override गर्छ।

   सिद्धान्त: colored background → सही text color variable प्रयोग गर्नु
   ══════════════════════════════════════════════════════════════════════ */

/* ── A. TOPBAR / HEADER ─────────────────────────────────────── */
.top-bar, .topbar, .site-topbar,
.header-top, .navbar-top, .quick-links-bar,
.header-utility-bar                            {
    background-color: var(--header-color) !important;
    color:            var(--text-on-header) !important;
}
.top-bar *,  .topbar *,  .site-topbar *,
.header-top *, .navbar-top *, .quick-links-bar *,
.header-utility-bar *                          {
    color: var(--text-on-header) !important;
}
/* Links inside topbar get own contrast colour */
.top-bar a, .topbar a, .site-topbar a,
.header-top a, .quick-links-bar a             {
    color: var(--text-on-header) !important;
}
.top-bar a:hover, .topbar a:hover,
.site-topbar a:hover, .quick-links-bar a:hover {
    opacity: .82 !important;
    color:   var(--text-on-header) !important;
}
/* Icons stay same colour */
.top-bar i, .topbar i, .site-topbar i         {
    color: var(--text-on-header) !important;
}

/* ── B. FOOTER ───────────────────────────────────────────────── */
footer, .site-footer, .footer-main,
.main-footer, .footer-wrap, .footer-top        {
    background-color: var(--footer-color) !important;
    color:            var(--text-on-footer) !important;
}
footer *, .site-footer *, .footer-main *,
.main-footer *, .footer-wrap *                 {
    color: var(--text-on-footer) !important;
}
footer a, .site-footer a, .footer-main a,
.main-footer a                                 {
    color: color-mix(in srgb, var(--text-on-footer) 88%, transparent) !important;
}
footer a:hover, .site-footer a:hover,
.footer-main a:hover                           {
    color: var(--text-on-footer) !important;
    opacity: .9 !important;
}
footer h1, footer h2, footer h3, footer h4,
footer h5, footer h6,
.site-footer h1, .site-footer h2, .site-footer h3,
.site-footer h4, .site-footer h5, .site-footer h6,
.footer-main h1, .footer-main h2, .footer-main h3,
.footer-main h4, .footer-main h5, .footer-main h6  {
    color: var(--text-on-footer) !important;
}

/* ── C. HERO / SLIDER SECTION ───────────────────────────────── */
.slider-section, .hero-section, .hero-wrap,
.slider-wrap, .main-slider, .hero-area          {
    color: var(--text-on-primary) !important;
}
/* ALL children of slider/hero get contrasted text */
.slider-section h1, .slider-section h2,
.slider-section h3, .slider-section p,
.slider-section span:not(.btn):not([class*="badge"]),
.hero-section h1, .hero-section h2,
.hero-section h3, .hero-section p               {
    color: var(--text-on-primary) !important;
    text-shadow: 0 1px 6px rgba(0,0,0,.22) !important;
}
/* slider-content direct descendants */
.slider-content                                 {
    color: var(--text-on-primary) !important;
}
.slider-content h1, .slider-content h2,
.slider-content h3, .slider-content h4,
.slider-content p                               {
    color: var(--text-on-primary) !important;
    text-shadow: 0 1px 8px rgba(0,0,0,.25) !important;
}
/* Hero modern classes — highest-priority */
.hero-title-modern                              {
    color: var(--text-on-primary) !important;
    text-shadow: 0 2px 10px rgba(0,0,0,.30) !important;
}
.hero-subtitle-modern                           {
    color: color-mix(in srgb, var(--text-on-primary) 93%, transparent) !important;
    text-shadow: 0 1px 6px rgba(0,0,0,.20) !important;
}
.hero-text-wrapper, .hero-content-modern,
.hero-text-block                                {
    color: var(--text-on-primary) !important;
}
.hero-text-wrapper *,  .hero-content-modern *,
.hero-text-block *                              {
    color: var(--text-on-primary) !important;
}
.hero-badge                                     {
    background: rgba(255,255,255,.18) !important;
    color: var(--text-on-primary) !important;
    border-color: rgba(255,255,255,.35) !important;
}
/* Slider button — accent colour so it pops against hero bg */
.slider-content .btn,
.hero-btn-modern                                {
    background-color: var(--secondary-color) !important;
    border-color:     var(--secondary-color) !important;
    color:            var(--text-on-secondary) !important;
}
.slider-content .btn:hover,
.hero-btn-modern:hover                          {
    filter: brightness(1.12) !important;
    color:  var(--text-on-secondary) !important;
}
/* Auction hero */
.auc2-hero-section                              {
    color: var(--text-on-primary) !important;
}
.auc2-h, .auc2-sub                             {
    color: var(--text-on-primary) !important;
    text-shadow: 0 2px 8px rgba(0,0,0,.25) !important;
}

/* ── D. PAGE BANNER ─────────────────────────────────────────── */
.page-banner, .page-banner-modern,
.inner-banner, .page-header-banner              {
    color: var(--text-on-primary) !important;
}
.page-banner h1, .page-banner h2,
.page-banner-modern h1, .page-banner-modern h2,
.inner-banner h1, .page-header-banner h1        {
    color: var(--text-on-primary) !important;
    text-shadow: 0 1px 6px rgba(0,0,0,.20) !important;
}
.page-banner .breadcrumb-item,
.page-banner .breadcrumb-item a,
.page-banner .breadcrumb-item.active,
.page-banner-modern .breadcrumb-item,
.page-banner-modern .breadcrumb-item a,
.page-banner-modern .breadcrumb-item.active     {
    color: var(--text-on-primary) !important;
}
.page-banner .breadcrumb-item a:hover,
.page-banner-modern .breadcrumb-item a:hover    {
    opacity: .82 !important;
    color: var(--text-on-primary) !important;
}
.page-banner .breadcrumb-item + .breadcrumb-item::before,
.page-banner-modern .breadcrumb-item + .breadcrumb-item::before {
    color: color-mix(in srgb, var(--text-on-primary) 65%, transparent) !important;
}

/* ── E. CTA SECTION ─────────────────────────────────────────── */
.cta-section, .cta-block, .cta-area,
.call-to-action                                 {
    color: var(--text-on-primary) !important;
}
.cta-content, .cta-text                         {
    color: var(--text-on-primary) !important;
}
.cta-content h1, .cta-content h2,
.cta-content h3, .cta-content p,
.cta-content span                               {
    color: var(--text-on-primary) !important;
}

/* ── F. MOBILE APP / DOWNLOAD SECTION ──────────────────────── */
.mobile-app-section, .app-download-section      {
    color: var(--text-on-primary) !important;
}
.mobile-app-section h1, .mobile-app-section h2,
.mobile-app-section h3, .mobile-app-section h4,
.mobile-app-section p, .mobile-app-section span,
.mobile-app-section .app-tagline,
.mobile-app-section .app-description,
.mobile-app-section .app-content *              {
    color: var(--text-on-primary) !important;
}

/* ── G. STAT BOXES ON BRAND BG ──────────────────────────────── */
.stat-box, .stat-number, .stat-label-brand,
.coop-stat-box, .stat-card-primary              {
    color: var(--text-on-primary) !important;
}
.stat-box *, .coop-stat-box *                   {
    color: var(--text-on-primary) !important;
}

/* ── H. SECTION BADGE ───────────────────────────────────────── */
.section-badge                                  {
    background-color: var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}

/* ── I. RATES SECTION HEADERS ───────────────────────────────── */
.rates-header, .rates-header-modern             {
    color: var(--text-on-primary) !important;
}
.rates-header h1, .rates-header h2, .rates-header h3,
.rates-header p,
.rates-header-modern h1, .rates-header-modern h2,
.rates-header-modern h3, .rates-header-modern p {
    color: var(--text-on-primary) !important;
}

/* ── J. BOOTSTRAP BG-* UTILITIES — brand-colour overrides ──── */
.bg-primary                                     {
    background-color: var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}
.bg-primary *                                   {
    color: var(--text-on-primary) !important;
}
.bg-secondary                                   {
    background-color: var(--secondary-color) !important;
    color:            var(--text-on-secondary) !important;
}
.bg-secondary *                                 {
    color: var(--text-on-secondary) !important;
}

/* ── K. BUTTONS ─────────────────────────────────────────────── */
.btn-primary, [class*="btn-coop-primary"]       {
    background-color: var(--primary-color) !important;
    border-color:     var(--primary-dark) !important;
    color:            var(--text-on-primary) !important;
}
.btn-primary:hover, .btn-primary:focus          {
    background-color: var(--primary-dark) !important;
    color:            var(--text-on-primary) !important;
}
.btn-outline-primary                            {
    border-color: var(--primary-color) !important;
    color:        var(--primary-color) !important;
}
.btn-outline-primary:hover,
.btn-outline-primary:focus                      {
    background-color: var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}
.btn-secondary, [class*="btn-coop-secondary"]   {
    background-color: var(--secondary-color) !important;
    border-color:     var(--secondary-dark) !important;
    color:            var(--text-on-secondary) !important;
}

/* ── L. BADGES / PILLS ──────────────────────────────────────── */
.badge.bg-primary, .badge-primary               {
    background-color: var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}
.badge.bg-secondary, .badge-secondary           {
    background-color: var(--secondary-color) !important;
    color:            var(--text-on-secondary) !important;
}

/* ── M. ADMIN SIDEBAR ───────────────────────────────────────── */
.admin-sidebar, .sidebar-dark, #adminSidebar    {
    background-color: var(--primary-dark) !important;
    color:            var(--text-on-primary) !important;
}
.admin-sidebar a, .sidebar-dark a               {
    color: color-mix(in srgb, var(--text-on-primary) 85%, transparent) !important;
}
.admin-sidebar a:hover, .sidebar-dark a:hover,
.admin-sidebar .active, .sidebar-dark .active   {
    color:            var(--text-on-primary) !important;
    background-color: rgba(255,255,255,.10) !important;
}
.admin-sidebar .sidebar-brand-text              {
    color: var(--text-on-primary) !important;
}

/* ── N. MEMBER TOPBAR ───────────────────────────────────────── */
.member-topbar, .mem-header, .mem-nav-bar       {
    background-color: var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}
.member-topbar *, .mem-header *, .mem-nav-bar * {
    color: var(--text-on-primary) !important;
}

/* ── O. TABS / NAV ON PRIMARY ───────────────────────────────── */
.nav-tabs .nav-link.active,
.nav-pills .nav-link.active                     {
    background-color: var(--primary-color) !important;
    border-color:     var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}
.page-item.active .page-link                    {
    background-color: var(--primary-color) !important;
    border-color:     var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}

/* ── P. CARD HEADERS WITH BRAND COLOR ──────────────────────── */
.card-header.bg-primary,
.card-header.bg-secondary                       {
    color: var(--text-on-primary) !important;
}
.card-header.bg-secondary                       {
    background-color: var(--secondary-color) !important;
    color:            var(--text-on-secondary) !important;
}

/* ── Q. AUTH / LOGIN PORTAL BACKGROUND ─────────────────────── */
body.auth-portal-page                           {
    background: linear-gradient(160deg,
        color-mix(in srgb, var(--primary-color, #1a5f2a) 9%, var(--bg-page, #f8faf9)),
        var(--bg-page, #f8faf9) 55%,
        color-mix(in srgb, var(--primary-color, #1a5f2a) 5%, var(--bg-page, #f8faf9))
    ) !important;
    color: var(--text-primary, #1a2e1f) !important;
    min-height: 100dvh;
}

/* ── R. VERIFY PAGE LOGO ────────────────────────────────────── */
.vp-page-logo img,
body.auth-portal-page .vp-page-logo img         {
    max-height: 70px !important;
    max-width:  200px !important;
    width:  auto !important;
    height: auto !important;
    object-fit: contain !important;
}

/* ── S. ALERT MESSAGES ──────────────────────────────────────── */
.alert-error,  .alert-danger, .coop-alert-error  {
    background: var(--color-danger-bg,  #fef2f2) !important;
    border-color:var(--color-danger-border,#fecaca) !important;
    color:       var(--color-danger,    #b91c1c) !important;
}
.alert-success, .coop-alert-success              {
    background: var(--color-success-bg, #f0fdf4) !important;
    border-color:var(--color-success-border,#bbf7d0) !important;
    color:       var(--color-success,   #15803d) !important;
}
.alert-warning, .coop-alert-warning              {
    background: var(--color-warning-bg, #fffbeb) !important;
    border-color:var(--color-warning-border,#fde68a) !important;
    color:       var(--color-warning,   #92400e) !important;
}
.alert-info,    .coop-alert-info                 {
    background: var(--color-info-bg,    #eff6ff) !important;
    border-color:var(--color-info-border,#bfdbfe) !important;
    color:       var(--color-info,      #1e40af) !important;
}

/* ── T. FORM FIELDS ─────────────────────────────────────────── */
.form-control, .form-select, .form-control-sm   {
    border-color: var(--border-color) !important;
    background:   var(--bg-card)      !important;
    color:        var(--text-primary)  !important;
}
.form-control::placeholder                      { color: var(--text-light) !important; }
.form-label, label.form-label                   { color: var(--text-secondary) !important; }
.form-text                                      { color: var(--text-muted) !important; }
.input-group-text                               {
    background:   var(--bg-soft)     !important;
    border-color: var(--border-color)!important;
    color:        var(--text-secondary)!important;
}

/* ── U. DROPDOWNS ───────────────────────────────────────────── */
.dropdown-menu                                  {
    border-color: var(--border-color) !important;
    background:   var(--bg-card)      !important;
}
.dropdown-item                                  { color: var(--text-primary) !important; }
.dropdown-item:hover, .dropdown-item:focus      {
    background: var(--bg-hover)    !important;
    color:      var(--primary-color)!important;
}
.dropdown-item.active, .dropdown-item:active    {
    background: var(--primary-color)      !important;
    color:      var(--text-on-primary)    !important;
}

/* ── V. TABLES ──────────────────────────────────────────────── */
.table th                                       { color: var(--text-secondary) !important; }
.table-hover tbody tr:hover td                  {
    background-color: var(--bg-hover) !important;
    color:            var(--text-primary) !important;
}

/* ── W. OAUTH / SOCIAL BUTTONS ──────────────────────────────── */
.oauth-btn                                      {
    border-color: var(--border-color) !important;
    background:   var(--bg-soft)      !important;
    color:        var(--text-primary)  !important;
}
.oauth-btn:hover                                {
    background:   var(--bg-card)       !important;
    border-color: var(--primary-color) !important;
}

/* ── X. PAGINATION ──────────────────────────────────────────── */
.page-link                                      {
    border-color: var(--border-color)  !important;
    color:        var(--primary-color) !important;
}
.page-link:hover                                {
    background:   var(--bg-hover)      !important;
    border-color: var(--primary-color) !important;
    color:        var(--primary-color) !important;
}
.page-item.disabled .page-link                  {
    color:      var(--text-light) !important;
    background: var(--bg-soft)    !important;
}

/* ── Y. DARK MODE (body.dark-mode) ──────────────────────────── */
body.dark-mode .page-banner,
body.dark-mode .slider-section,
body.dark-mode .hero-section                    {
    color: var(--text-on-primary) !important;
}


/* ══════════════════════════════════════════════════════════════════════
   HOVER & ICON CONTRAST PATCH
   ──────────────────────────────────────────────────────────────────────
   app-public.css मा केही hover states ले brand-color background लगाउँछन्
   तर text/icon color update गर्दैनन् → dark text on dark bg।
   यो block ले ती सबैलाई fix गर्छ।
   ══════════════════════════════════════════════════════════════════════ */

/* ── Z1. NAV-MENU — active/hover link ───────────────────────── */
.nav-menu > li > a:hover,
.nav-menu > li.active > a                       {
    background-color: var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}
.nav-menu > li > a:hover i,
.nav-menu > li.active > a i                     {
    color: var(--text-on-primary) !important;
}

/* ── Z2. NAV-PILLS / SHOW active (app-core L354 missing color) */
.nav-pills .nav-link.active,
.nav-pills .show > .nav-link                    {
    background-color: var(--primary-color) !important;
    color:            var(--text-on-primary) !important;
}

/* ── Z3. SCROLL BUTTON (floating scroll-to-top / scroll-down) – */
.scroll-btn.up, .scroll-btn.up:hover            {
    color: var(--text-on-primary) !important;
}
.scroll-btn.up i, .scroll-btn.up:hover i        {
    color: var(--text-on-primary) !important;
}

/* ── Z4. CAROUSEL CONTROLS on hero slider ───────────────────── */
.hero-slider .carousel-control-prev:hover,
.hero-slider .carousel-control-next:hover       {
    background: var(--secondary-color) !important;
    color:      var(--text-on-secondary) !important;
    opacity: 1 !important;
}
.hero-slider .carousel-control-prev:hover .carousel-control-prev-icon,
.hero-slider .carousel-control-next:hover .carousel-control-next-icon,
.hero-slider .carousel-control-prev:hover i,
.hero-slider .carousel-control-next:hover i     {
    filter: none !important;
    color:  var(--text-on-secondary) !important;
}

/* ── Z5. TOPBAR DARK-MODE / SEARCH BUTTON HOVER ─────────────── */
.social-links .topbar-darkmode-btn a:hover,
.social-links .topbar-search-btn a:hover        {
    background: var(--secondary-color) !important;
    color:      var(--text-on-secondary) !important;
}
.social-links .topbar-darkmode-btn a:hover i,
.social-links .topbar-search-btn a:hover i      {
    color: var(--text-on-secondary) !important;
}

/* ── Z6. TOOL-WIDGET CARD HOVER — all child text ────────────── */
.tool-widget-card:hover,
.tool-widget-card.highlight-widget:hover        {
    background: var(--primary-color) !important;
    color:      var(--text-on-primary) !important;
}
.tool-widget-card:hover h5,
.tool-widget-card.highlight-widget:hover h5,
.tool-widget-card:hover p,
.tool-widget-card.highlight-widget:hover p,
.tool-widget-card:hover span:not(.widget-icon),
.tool-widget-card.highlight-widget:hover span:not(.widget-icon),
.tool-widget-card:hover .widget-label,
.tool-widget-card:hover .widget-desc           {
    color: var(--text-on-primary) !important;
}
/* Widget icon container stays white so icon colour (primary) pops */
.tool-widget-card:hover .widget-icon,
.tool-widget-card.highlight-widget:hover .widget-icon {
    background: rgba(255,255,255,.18) !important;
}
.tool-widget-card:hover .widget-icon i,
.tool-widget-card.highlight-widget:hover .widget-icon i {
    color: var(--text-on-primary) !important;
}

/* ── Z7. NDP DATE PICKER — selected/hover day ───────────────── */
.ndp-container .ndp-day.selected,
.ndp-container .ndp-day:hover                   {
    background: var(--primary-color) !important;
    color:      var(--text-on-primary) !important;
    border-radius: 50% !important;
}

/* ── Z8. SERVICE ICON HOVERS (use var, not hardcoded #fff) ───── */
/* quick-service-card */
.quick-service-card:hover .service-icon         {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
}
.quick-service-card:hover .service-icon i       {
    color: var(--text-on-primary) !important;
}
/* service-card / service-card-modern */
.service-card:hover .service-icon,
.service-card-modern:hover .service-icon-modern {
    background: var(--primary-color) !important;
}
.service-card:hover .service-icon i,
.service-card-modern:hover .service-icon-modern i {
    color: var(--text-on-primary) !important;
}
/* feature-box */
.feature-box:hover .feature-icon                {
    background: var(--primary-color) !important;
}
.feature-box:hover .feature-icon i              {
    color: var(--text-on-primary) !important;
}
/* file-upload icon */
.file-upload-enhanced:hover .upload-icon        {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
}
.file-upload-enhanced:hover .upload-icon i,
.file-upload-enhanced:hover .upload-icon svg    {
    color: var(--text-on-primary) !important;
}

/* ── Z9. STAT CARD ICON (already #fff hardcoded — add safety) ── */
.stat-card .stat-icon, .stat-card .stat-icon i  {
    color: var(--text-on-primary) !important;
}

/* ── Z10. BTN-PRIMARY HOVER — gradient light→dark needs contrast */
.btn-primary:hover, .btn-primary:focus          {
    background: linear-gradient(135deg,
        var(--primary-dark),
        var(--primary-color)) !important;
    color: var(--text-on-primary) !important;
    border-color: var(--primary-dark) !important;
}
.btn-primary:hover i, .btn-primary:focus i      {
    color: var(--text-on-primary) !important;
}

/* ── Z11. ADMIN SIDEBAR LINK ACTIVE ─────────────────────────── */
.sidebar-nav a.active,
.admin-nav a.active,
.sidebar-menu a.active                          {
    background: var(--primary-color) !important;
    color:      var(--text-on-primary) !important;
}

/* ── Z12. RATE-VALUE / RATE-CARD SAVING/LOAN CONTEXT ────────── */
/* These are on dark-coloured backgrounds → keep white but use var */
.rates-icon.saving, .rates-icon.loan,
.rate-value.saving, .rate-value.loan            {
    color: var(--text-on-primary) !important;
}

/* ── Z13. CONTACT SECTION — text on primary-coloured bg ─────── */
.contact-details, .contact-details p,
.contact-details p a, .contact-info-box .contact-details p a {
    color: var(--text-on-primary) !important;
}
.contact-details p a:hover                      {
    opacity: .82 !important;
    color: var(--text-on-primary) !important;
}
.contact-icon, .contact-icon i                  {
    color: var(--text-on-primary) !important;
}

/* ── Z14. SECTION TOOLS / CATEGORY CARDS ────────────────────── */
.tools-category-card h5,
.tools-category-card h5 i                       {
    color: var(--text-on-primary) !important;
}

/* ── Z15. FISCAL YEAR / INSTITUTIONAL STATS BADGES ──────────── */
.institutional-stats-section .fiscal-year-badge,
.home-ip-preview-head, .home-ip-rich-header     {
    color: var(--text-on-primary) !important;
}

/* ── Z16. CHATBOT WIDGET ────────────────────────────────────── */
.chatbot-toggle                                 {
    background: var(--primary-color) !important;
    color:      var(--text-on-primary) !important;
}
.chatbot-header                                 {
    background: var(--primary-color) !important;
    color:      var(--text-on-primary) !important;
}
.chatbot-header *, .chatbot-close              {
    color: var(--text-on-primary) !important;
}

/* ── Z17. --white safety fallback (should always resolve) ────── */
:root {
    --white: #ffffff;
    --black: #000000;
}


/* ══════════════════════════════════════════════════════════════════════
   AA. AUTH PORTAL — UNIFORM CARD + BODY (admin-auth, member, verify)
   ─────────────────────────────────────────────────────────────────────
   admin/index.php loads app-admin.css only (no app-member.css) so the
   auth-card styles from app-member.css are MISSING there. We reproduce
   the essential card rules here so global-theme.php covers every panel.
   ══════════════════════════════════════════════════════════════════════ */

/* Centering shell */
body.auth-portal-page                               {
    display:         flex !important;
    flex-direction:  column !important;
    align-items:     center !important;
    justify-content: center !important;
    padding:         60px 16px 24px !important;
    font-family:     var(--font-primary,'Mukta','Noto Sans Devanagari','Segoe UI',sans-serif) !important;
}
body.auth-portal-page.verify-auth-page              {
    align-items:     center !important;
    padding-top:     16px !important;
}

/* Auth card — uniform across admin / member */
body.auth-portal-page .auth-card,
body.auth-portal-page .verify-form-card            {
    width:         100% !important;
    max-width:     400px !important;
    border-radius: 16px !important;
    border:        1px solid var(--border-color, #e5e7eb) !important;
    box-shadow:
        0 2px 6px color-mix(in srgb, var(--primary-color,#1a5f2a) 6%, transparent),
        0 18px 40px color-mix(in srgb, var(--text-primary,#1a2e1f) 7%, transparent) !important;
    background:    var(--bg-card, #fff) !important;
    overflow:      hidden !important;
}
body.auth-portal-page.admin-auth-page .auth-card    {
    max-width: 420px !important;
}

/* Card header */
body.auth-portal-page .card-header,
body.auth-portal-page .verify-form-card__head       {
    padding:       1.4rem 1.5rem 1rem !important;
    text-align:    center !important;
    border-bottom: 1px solid var(--border-soft, #f0f0f0) !important;
    background:    var(--bg-card, #fff) !important;
}

/* Logo image inside card */
body.auth-portal-page .card-logo-wrap img           {
    max-height:   54px !important;
    max-width:    180px !important;
    object-fit:   contain !important;
    border-radius: 8px !important;
    display:      block !important;
    margin:       0 auto 0.5rem !important;
}

/* Portal label */
body.auth-portal-page .card-portal-label            {
    display:         inline-flex !important;
    align-items:     center !important;
    gap:             5px !important;
    background:      transparent !important;
    border:          none !important;
    color:           var(--text-muted, #6b7280) !important;
    font-size:       0.7rem !important;
    font-weight:     600 !important;
    letter-spacing:  0.04em !important;
}

/* Card body */
body.auth-portal-page .card-body,
body.auth-portal-page .verify-form-card__body       {
    padding: 1.2rem 1.5rem 1.5rem !important;
}

/* Title / subtitle */
body.auth-portal-page .card-title                   {
    font-size:    1.08rem !important;
    font-weight:  700 !important;
    text-align:   center !important;
    color:        var(--text-primary, #1a2e1f) !important;
    margin-bottom: 3px !important;
    line-height:  1.4 !important;
}
body.auth-portal-page .card-sub                     {
    font-size:    0.79rem !important;
    color:        var(--text-muted, #6b7280) !important;
    text-align:   center !important;
    margin-bottom: 1rem !important;
    line-height:  1.55 !important;
}

/* Tabs */
body.auth-portal-page .tabs                        {
    display:       flex !important;
    gap:           5px !important;
    padding:       5px !important;
    border-radius: 12px !important;
    border:        1px solid var(--border-color, #e5e7eb) !important;
    background:    var(--bg-soft, #f5faf6) !important;
    margin-bottom: 16px !important;
}
body.auth-portal-page .tab-btn                      {
    flex:          1 !important;
    min-height:    34px !important;
    padding:       0 8px !important;
    border:        none !important;
    border-radius: 9px !important;
    background:    transparent !important;
    color:         var(--text-muted, #6b7280) !important;
    font-size:     0.83rem !important;
    font-weight:   600 !important;
    cursor:        pointer !important;
    display:       inline-flex !important;
    align-items:   center !important;
    justify-content: center !important;
    gap:           6px !important;
    transition:    all 0.15s !important;
}
body.auth-portal-page .tab-btn.active               {
    background:    var(--bg-card, #fff) !important;
    color:         var(--primary-color, #1a5f2a) !important;
    box-shadow:    0 1px 4px rgba(0,0,0,.08) !important;
}

/* Fields */
body.auth-portal-page .field,
body.auth-portal-page .vp-field,
body.auth-portal-page .input-icon                   {
    width:         100% !important;
    margin-bottom: 14px !important;
}
body.auth-portal-page .field label,
body.auth-portal-page .vp-field label               {
    display:       block !important;
    font-size:     0.79rem !important;
    font-weight:   600 !important;
    color:         var(--text-secondary, #4a5a4f) !important;
    margin-bottom: 7px !important;
    letter-spacing: 0.03em !important;
}
body.auth-portal-page .field input,
body.auth-portal-page .field select,
body.auth-portal-page .field textarea,
body.auth-portal-page .vp-field input,
body.auth-portal-page .vp-field select,
body.auth-portal-page .vp-field textarea,
body.auth-portal-page .vp-field-input              {
    width:         100% !important;
    min-height:    44px !important;
    padding:       10px 14px !important;
    border:        1px solid var(--border-color, #e5e7eb) !important;
    border-radius: 10px !important;
    background:    var(--bg-soft, #f5faf6) !important;
    color:         var(--text-primary, #1a2e1f) !important;
    font-size:     0.9rem !important;
    font-family:   inherit !important;
    box-sizing:    border-box !important;
    transition:    border-color 0.15s, box-shadow 0.15s !important;
}
body.auth-portal-page .field input:focus,
body.auth-portal-page .vp-field input:focus         {
    border-color:  var(--primary-color, #1a5f2a) !important;
    background:    var(--bg-card, #fff) !important;
    box-shadow:    0 0 0 3px color-mix(in srgb,var(--primary-color,#1a5f2a) 16%,transparent) !important;
    outline:       none !important;
}

/* Input icon wrapper */
body.auth-portal-page .input-icon                   {
    position: relative !important;
    display:  block !important;
}
body.auth-portal-page .input-icon .input-icon-left  {
    position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
    color: var(--text-muted,#6b7280); font-size: .85rem; pointer-events: none;
}
body.auth-portal-page .input-icon input             {
    padding-left: 38px !important;
}

/* Submit button */
body.auth-portal-page .submit-btn,
body.auth-portal-page .vp-btn                       {
    width:         100% !important;
    min-height:    46px !important;
    padding:       11px 20px !important;
    border:        none !important;
    border-radius: 10px !important;
    background:    var(--primary-color, #1a5f2a) !important;
    color:         var(--text-on-primary, #fff) !important;
    font-size:     0.93rem !important;
    font-weight:   700 !important;
    cursor:        pointer !important;
    box-shadow:    0 2px 10px color-mix(in srgb,var(--primary-color,#1a5f2a) 24%,transparent) !important;
    transition:    background 0.18s, transform 0.12s !important;
    display:       flex !important;
    align-items:   center !important;
    justify-content: center !important;
    gap:           8px !important;
    margin-top:    4px !important;
}
body.auth-portal-page .submit-btn:hover,
body.auth-portal-page .vp-btn:hover                 {
    background: var(--primary-dark, #134d22) !important;
    transform:  translateY(-1px) !important;
    box-shadow: 0 4px 14px color-mix(in srgb,var(--primary-color,#1a5f2a) 30%,transparent) !important;
}
body.auth-portal-page .submit-btn:active,
body.auth-portal-page .vp-btn:active                {
    transform: none !important;
}

/* Foot link */
body.auth-portal-page .foot-link                    {
    text-align:   center !important;
    margin-top:   1rem !important;
    font-size:    0.79rem !important;
    color:        var(--text-muted, #6b7280) !important;
}
body.auth-portal-page .foot-link a                  {
    color:       var(--primary-color, #1a5f2a) !important;
    font-weight: 600 !important;
    text-decoration: none !important;
}
body.auth-portal-page .foot-link a:hover            {
    text-decoration: underline !important;
}

/* Alerts inside auth cards */
body.auth-portal-page .alert,
body.auth-portal-page .alert-error,
body.auth-portal-page .alert-success,
body.auth-portal-page .alert-warning,
body.auth-portal-page .alert-info                   {
    border-radius: 10px !important;
    padding:       10px 14px !important;
    margin-bottom: 13px !important;
    font-size:     0.82rem !important;
    display:       flex !important;
    align-items:   flex-start !important;
    gap:           9px !important;
    line-height:   1.5 !important;
}

/* Password toggle button */
body.auth-portal-page .pw-wrap                      {
    position: relative !important;
    width: 100% !important;
}
body.auth-portal-page .pw-wrap input                { padding-right: 44px !important; }
body.auth-portal-page .pw-toggle                    {
    position:   absolute !important;
    right:      12px !important;
    top:        50% !important;
    transform:  translateY(-50%) !important;
    background: none !important;
    border:     none !important;
    cursor:     pointer !important;
    color:      var(--text-muted,#6b7280) !important;
    padding:    4px !important;
    font-size:  0.9rem !important;
}
body.auth-portal-page .pw-toggle:hover              {
    color: var(--primary-color,#1a5f2a) !important;
}

/* Forgot-password link */
body.auth-portal-page .forgot-link,
body.auth-portal-page a.forgot-link                 {
    color:       var(--primary-color,#1a5f2a) !important;
    font-size:   0.79rem !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    display:     block !important;
    text-align:  right !important;
    margin-bottom: 12px !important;
}

/* Security note (admin) */
body.auth-portal-page .security-note                {
    background:    color-mix(in srgb,var(--primary-color,#1a5f2a) 7%,var(--bg-soft,#f5faf6)) !important;
    border:        1px solid color-mix(in srgb,var(--primary-color,#1a5f2a) 16%,var(--border-color,#e5e7eb)) !important;
    border-radius: 10px !important;
    padding:       10px 14px !important;
    font-size:     0.78rem !important;
    color:         var(--text-secondary, #4a5a4f) !important;
    margin-top:    14px !important;
}
body.auth-portal-page .security-note i              {
    color: var(--primary-color,#1a5f2a) !important;
}

/* Back link + lang toggle */
body.auth-portal-page:not(.verify-auth-page) .page-back,
body.auth-portal-page:not(.verify-auth-page) .auth-lang-toggle {
    position: fixed !important;
    top: 16px !important;
    z-index: 20 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 15px !important;
    border-radius: 999px !important;
    font-size: 0.79rem !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    border: 1px solid var(--border-color,#e5e7eb) !important;
    background: rgba(255,255,255,.88) !important;
    backdrop-filter: blur(8px) !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.07) !important;
    color: var(--primary-color,#1a5f2a) !important;
    transition: background 0.15s !important;
}
body.auth-portal-page:not(.verify-auth-page) .page-back   { right: 20px !important; }
body.auth-portal-page:not(.verify-auth-page) .auth-lang-toggle { left: 20px !important; }
body.auth-portal-page:not(.verify-auth-page) .page-back:hover,
body.auth-portal-page:not(.verify-auth-page) .auth-lang-toggle:hover {
    background: var(--bg-card,#fff) !important;
    border-color: color-mix(in srgb,var(--primary-color,#1a5f2a) 22%,var(--border-color,#e5e7eb)) !important;
}

/* ══════════════════════════════════════════════════════════════════════
   AB. FORM FIELD UNIFORMITY — admin + member panels
   ─────────────────────────────────────────────────────────────────────
   Bootstrap and browser defaults cause wildly inconsistent field heights:
   native <input type="date"> is taller/shorter than text inputs, selects
   have different border-radius, etc. This block enforces a uniform 42px
   height, 8px radius, and consistent border across ALL admin/member forms.
   ══════════════════════════════════════════════════════════════════════ */

/* ── Uniform base for all non-auth-portal inputs ────────────────── */
.admin-shell .form-control,
.admin-shell .form-select,
.admin-shell input[type="text"],
.admin-shell input[type="email"],
.admin-shell input[type="number"],
.admin-shell input[type="tel"],
.admin-shell input[type="url"],
.admin-shell input[type="search"],
.admin-shell input[type="password"],
.admin-shell input[type="date"],
.admin-shell input[type="time"],
.admin-shell input[type="month"],
.admin-shell input[type="week"],
.admin-shell input[type="datetime-local"],
.admin-shell select,
.admin-shell textarea                               {
    min-height:    42px !important;
    border-radius: 8px !important;
    border:        1px solid var(--border, #e2e8f0) !important;
    font-size:     0.9rem !important;
    font-family:   var(--font-primary,'Mukta','Noto Sans Devanagari',sans-serif) !important;
    padding-top:   8px !important;
    padding-bottom: 8px !important;
    color:         var(--text-primary,#1a2e1f) !important;
    background-color: var(--surface,#fff) !important;
}

/* ── Date input gets the same treatment (overrides browser chrome) ─ */
.admin-shell input[type="date"],
.admin-shell input[type="time"],
.admin-shell input[type="datetime-local"],
.admin-shell input[type="month"]                    {
    -webkit-appearance: none !important;
    appearance:         none !important;
    padding:            8px 12px !important;
    line-height:        normal !important;
    width:              100% !important;
}

/* ── Member panel (same rule set with .member-body or body.member-page) */
.member-page .form-control,
.member-page .form-select,
.member-page input[type="text"],
.member-page input[type="email"],
.member-page input[type="number"],
.member-page input[type="tel"],
.member-page input[type="date"],
.member-page input[type="time"],
.member-page input[type="datetime-local"],
.member-page select                                 {
    min-height:    42px !important;
    border-radius: 8px !important;
    border:        1px solid var(--border-color,#e5e7eb) !important;
    font-size:     0.9rem !important;
    font-family:   var(--font-primary,'Mukta','Noto Sans Devanagari',sans-serif) !important;
}

/* ── Uniform focus ring (brand colour) ──────────────────────────── */
.admin-shell .form-control:focus,
.admin-shell .form-select:focus,
.admin-shell input:focus,
.admin-shell select:focus,
.admin-shell textarea:focus                         {
    border-color: var(--primary-color,#1a5f2a) !important;
    box-shadow:   0 0 0 3px color-mix(in srgb,var(--primary-color,#1a5f2a) 15%,transparent) !important;
    outline:      none !important;
}

/* ── Form labels — uniform size/weight ──────────────────────────── */
.admin-shell .form-label,
.admin-shell label                                  {
    font-size:   0.82rem !important;
    font-weight: 600 !important;
    color:       var(--text-secondary,#4a5a4f) !important;
    margin-bottom: 6px !important;
    display:     block !important;
}

/* ── Form groups — consistent spacing ───────────────────────────── */
.admin-shell .mb-3,
.admin-shell .mb-4                                  {
    margin-bottom: 1.1rem !important;
}

/* ── Inline input-group (BS date + calendar icon btn) ───────────── */
.admin-shell .input-group .form-control,
.admin-shell .input-group input                     {
    border-right: none !important;
    border-radius: 8px 0 0 8px !important;
}
.admin-shell .input-group .input-group-text,
.admin-shell .input-group .btn                      {
    border-radius: 0 8px 8px 0 !important;
    border: 1px solid var(--border,#e2e8f0) !important;
    background: var(--surface-muted,#f1f5f9) !important;
    color: var(--primary-color,#1a5f2a) !important;
    min-height: 42px !important;
    padding: 0 13px !important;
}

/* ── Textarea uniform ────────────────────────────────────────────── */
.admin-shell textarea.form-control                  {
    min-height:   80px !important;
    resize:       vertical !important;
    padding-top:  10px !important;
}

/* ── Section card headers (used in admin profile edit form) ─────── */
.admin-shell .card,
.admin-shell .form-section                          {
    border-radius: 12px !important;
    border: 1px solid var(--border,#e2e8f0) !important;
    background: var(--surface,#fff) !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.05) !important;
    margin-bottom: 1.25rem !important;
}
.admin-shell .card-header,
.admin-shell .form-section-header                   {
    border-radius: 12px 12px 0 0 !important;
    padding: 0.9rem 1.25rem !important;
    font-size: 0.9rem !important;
    font-weight: 700 !important;
    border-bottom: 1px solid var(--border,#e2e8f0) !important;
    background: var(--surface-muted,#f8fafc) !important;
    color: var(--text-primary,#1a2e1f) !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.admin-shell .card-body,
.admin-shell .form-section-body                     {
    padding: 1.1rem 1.25rem !important;
}

/* ── Page banner contrast (all public pages) ─────────────────────── */
.page-banner, .page-header-section                  {
    background-color: var(--primary-color,#1a5f2a) !important;
}
.page-banner h1, .page-banner h2,
.page-banner .page-title,
.page-banner .breadcrumb-item,
.page-banner .breadcrumb-item a,
.page-banner .breadcrumb-item.active,
.page-banner li, .page-banner span,
.page-banner p, .page-banner *                      {
    color: var(--text-on-primary,#fff) !important;
}
.page-banner .breadcrumb-item + .breadcrumb-item::before {
    color: color-mix(in srgb,var(--text-on-primary,#fff) 65%,transparent) !important;
}

/* ── Verify page vp-outer centering ─────────────────────────────── */
body.verify-auth-page .vp-outer                     {
    width: 100% !important;
    max-width: 560px !important;
    margin: 0 auto !important;
}
body.verify-auth-page .vp-card                      {
    background: var(--bg-card,#fff) !important;
    border-radius: 14px !important;
    border: 1px solid var(--border-color,#e5e7eb) !important;
    box-shadow: 0 10px 30px rgba(0,0,0,.09) !important;
    padding: 1.5rem !important;
}

</style>
