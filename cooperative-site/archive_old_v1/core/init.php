<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * 🚀 CORE INIT — सबै Portal को एकमात्र Entry Point
 * ═══════════════════════════════════════════════════════════════
 * फाइल: core/init.php
 *
 * यो एउटा file include गरे पुग्छ — सबै portal को हरेक page मा।
 *
 * 📌 प्रयोग गर्ने तरिका:
 * ───────────────────────────────────────────────────────────────
 *
 *  Public pages (index.php, about.php, etc.):
 *    define('PORTAL', 'public');
 *    require_once __DIR__ . '/core/init.php';
 *
 *  Admin portal (admin/*.php):
 *    define('PORTAL', 'admin');
 *    define('IS_ADMIN_PAGE', true);
 *    require_once __DIR__ . '/../core/init.php';
 *
 *  Member portal (member/*.php):
 *    define('PORTAL', 'member');
 *    require_once __DIR__ . '/../core/init.php';
 *
 *  Verify portal (verify.php):
 *    define('PORTAL', 'verify');
 *    require_once __DIR__ . '/core/init.php';
 *
 * ✅ यसले गर्छ:
 *   - Output buffering सुरु
 *   - PHP version + extension check
 *   - Database connection (PDO)
 *   - Session start (secure)
 *   - CSRF protection
 *   - Security HTTP headers
 *   - Error handler (production-safe)
 *   - core/helpers.php load
 *   - panel-uniform.php load
 *   - auth-roles.php load
 *   - Portal-specific bootstrap
 * ═══════════════════════════════════════════════════════════════
 */

// ─── Duplicate load रोक्ने ───
if (defined('CORE_INIT_LOADED')) return;
define('CORE_INIT_LOADED', true);

// ─── Output buffering — header errors रोक्न ───
if (!ob_get_level()) {
    ob_start();
}

// ─── Portal detection — define गर्न बिर्सेमा default 'public' ───
if (!defined('PORTAL')) {
    define('PORTAL', 'public');
}

// ─── Root path detect — init.php कहाँ छ त्यसबाट ───
define('CORE_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__) . '/');

// ═══════════════════════════════════════════════════════════════
// STEP 1: PHP Version + Extension Check
// ═══════════════════════════════════════════════════════════════
if (file_exists(ROOT_PATH . 'includes/compatibility.php')) {
    require_once ROOT_PATH . 'includes/compatibility.php';
} else {
    // Inline fallback check
    if (version_compare(PHP_VERSION, '8.0', '<')) {
        http_response_code(500);
        die('<p style="font-family:sans-serif;padding:2rem;">⚠️ PHP 8.0+ चाहिन्छ। अहिले: PHP ' . PHP_VERSION . '</p>');
    }
    foreach (['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl'] as $_ext) {
        if (!extension_loaded($_ext)) {
            die("⚠️ PHP extension '{$_ext}' चाहिन्छ तर install भएको छैन।");
        }
    }
}

// ═══════════════════════════════════════════════════════════════
// STEP 2: Core Config (DB connection, getSetting, session, etc.)
// ═══════════════════════════════════════════════════════════════
require_once ROOT_PATH . 'includes/config.php';

// ═══════════════════════════════════════════════════════════════
// STEP 3: Core Helpers (date, currency, interest, sanitize, etc.)
// ═══════════════════════════════════════════════════════════════
require_once CORE_PATH . '/helpers.php';

// ═══════════════════════════════════════════════════════════════
// STEP 4: BS/AD Date Converter
// ═══════════════════════════════════════════════════════════════
if (file_exists(ROOT_PATH . 'includes/nepali-bs-convert.php')) {
    require_once ROOT_PATH . 'includes/nepali-bs-convert.php';
}

// ═══════════════════════════════════════════════════════════════
// STEP 5: Audit + Soft-delete helpers
// ═══════════════════════════════════════════════════════════════
if (file_exists(ROOT_PATH . 'includes/audit.php')) {
    require_once ROOT_PATH . 'includes/audit.php';
}

// ═══════════════════════════════════════════════════════════════
// STEP 6: Notification Templates
// ═══════════════════════════════════════════════════════════════
if (file_exists(ROOT_PATH . 'includes/notification-templates.php')) {
    require_once ROOT_PATH . 'includes/notification-templates.php';
}

// ═══════════════════════════════════════════════════════════════
// STEP 7: Role-Based Access Control
// ═══════════════════════════════════════════════════════════════
if (file_exists(ROOT_PATH . 'includes/auth-roles.php')) {
    require_once ROOT_PATH . 'includes/auth-roles.php';
}

// ═══════════════════════════════════════════════════════════════
// STEP 8: Cross-Panel Uniform UI helpers
// ═══════════════════════════════════════════════════════════════
if (file_exists(ROOT_PATH . 'includes/panel-uniform.php')) {
    require_once ROOT_PATH . 'includes/panel-uniform.php';
}

// ═══════════════════════════════════════════════════════════════
// STEP 9: Safe Query Helpers
// ═══════════════════════════════════════════════════════════════
if (file_exists(ROOT_PATH . 'includes/safe-query.php')) {
    require_once ROOT_PATH . 'includes/safe-query.php';
}

// ═══════════════════════════════════════════════════════════════
// STEP 10: Portal-Specific Bootstrap
// ═══════════════════════════════════════════════════════════════
switch (PORTAL) {

    case 'admin':
        // IS_ADMIN_PAGE define (admin-ui.php को security guard)
        if (!defined('IS_ADMIN_PAGE')) define('IS_ADMIN_PAGE', true);

        // Admin-specific tables ensure
        if (file_exists(ROOT_PATH . 'admin/includes/ensure-admin-tables.php')) {
            require_once ROOT_PATH . 'admin/includes/ensure-admin-tables.php';
        }
        // Admin UI helpers
        if (file_exists(ROOT_PATH . 'admin/includes/admin-ui.php')) {
            require_once ROOT_PATH . 'admin/includes/admin-ui.php';
        }
        // Notifications (email/SMS)
        if (file_exists(ROOT_PATH . 'includes/notifications.php')) {
            require_once ROOT_PATH . 'includes/notifications.php';
        }

        // DB not configured redirect (login र db-setup बाहेक)
        if (defined('DB_NAME') && DB_NAME === '') {
            $_curPage = basename($_SERVER['PHP_SELF'] ?? '');
            if (!in_array($_curPage, ['db-setup.php', 'index.php', 'login.php'], true)) {
                header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '/admin/') . 'db-setup.php');
                exit;
            }
        }

        // Admin auth check
        if (function_exists('isAdminLoggedIn') && !isAdminLoggedIn()) {
            $_curPage = basename($_SERVER['PHP_SELF'] ?? '');
            if (!in_array($_curPage, ['index.php', 'login.php', 'db-setup.php'], true)) {
                header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '/admin/') . 'index.php');
                exit;
            }
        }

        // CSRF check for all admin POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && function_exists('verifyCSRFToken')) {
            if (!verifyCSRFToken()) {
                if (function_exists('setFlash')) setFlash('error', 'सुरक्षा जाँच असफल। कृपया पुनः प्रयास गर्नुहोस्।');
                $referer = $_SERVER['HTTP_REFERER'] ?? (defined('ADMIN_URL') ? ADMIN_URL . 'dashboard.php' : '/admin/');
                header('Location: ' . $referer);
                exit;
            }
        }

        // Pre-generate CSRF token
        if (function_exists('generateCSRFToken')) {
            $GLOBALS['csrfToken'] = generateCSRFToken();
        }

        // Site license check (non-superadmin)
        $_licPage = basename($_SERVER['PHP_SELF'] ?? '');
        $_licExempt = in_array($_licPage, ['index.php', 'logout.php', 'site-license.php', 'site-license-blocked.php', 'db-setup.php'], true);
        if (!$_licExempt
            && function_exists('site_license_expired')
            && site_license_expired()
            && empty($_SESSION['is_superadmin'])) {
            header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '/admin/') . 'site-license-blocked.php');
            exit;
        }
        unset($_licPage, $_licExempt, $_curPage);
        break;

    case 'member':
        // Member auth helpers
        if (file_exists(ROOT_PATH . 'includes/member-auth.php')) {
            require_once ROOT_PATH . 'includes/member-auth.php';
        }
        // Member-specific security headers
        if (function_exists('memberSecurityHeaders')) {
            memberSecurityHeaders();
        }
        break;

    case 'verify':
        // Verify portal — minimal, no auth required
        break;

    case 'public':
    default:
        // Public site license guard
        if (function_exists('site_license_public_guard')) {
            site_license_public_guard();
        }
        // Member auth helpers (for nav badge, logged-in member check)
        if (file_exists(ROOT_PATH . 'includes/member-auth.php')) {
            require_once ROOT_PATH . 'includes/member-auth.php';
        }
        break;
}

// ═══════════════════════════════════════════════════════════════
// STEP 11: Global convenience variables (सबै portal मा available)
// ═══════════════════════════════════════════════════════════════

/** @var string $currentLang — 'np' वा 'en' */
$currentLang = function_exists('getCurrentLang') ? getCurrentLang() : 'np';

/** @var bool $isEnglish */
$isEnglish = ($currentLang === 'en');

/** @var \PDO|null $db — global DB handle */
try {
    $db = function_exists('getDB') ? getDB() : null;
} catch (\Throwable $e) {
    $db = null;
}

/** @var string $siteName */
$siteName = function_exists('getSetting')
    ? getSetting('site_name', 'आकाश सहकारी')
    : 'आकाश सहकारी';

/** @var string $siteNameEn */
$siteNameEn = function_exists('getSetting')
    ? getSetting('site_name_en', 'Aakash Cooperative')
    : 'Aakash Cooperative';

// ─── Translation shortcut — $t('नेपाली', 'English') ───
$t = static function (string $np, string $en) use ($isEnglish): string {
    return $isEnglish ? $en : $np;
};

// ─── CSRF token shortcut ───
$csrf = function_exists('generateCSRFToken') ? generateCSRFToken() : '';

// ═══════════════════════════════════════════════════════════════
// DONE — init.php successfully loaded
// ═══════════════════════════════════════════════════════════════
