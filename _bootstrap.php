<?php
/**
 * 🚀 BOOTSTRAP FILE — Central initialization
 * ═════════════════════════════════════════════════
 * यो file सबै requests को शुरुमा load हुन्छ।
 * Database, Auth, Config, Helpers सबै यहाँ setup हुन्छ।
 * ═════════════════════════════════════════════════
 */

// ─────────────────────────────────────────────────
// 1. CORE CONFIGURATION
// ─────────────────────────────────────────────────

// ─────────────────────────────────────────────────
// 0. ENVIRONMENT — define FIRST so all checks below work
// ─────────────────────────────────────────────────
if (!defined('ENVIRONMENT')) {
    $__env = strtolower(trim((string)(getenv('APP_ENV') ?: getenv('APPLICATION_ENV') ?: '')));
    define('ENVIRONMENT', in_array($__env, ['development', 'staging', 'production'], true) ? $__env : 'production');
    unset($__env);
}

// Set error reporting
if (defined('ENVIRONMENT')) {
    if (ENVIRONMENT === 'development') {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        ini_set('log_errors', 1);
        ini_set('log_errors_max_len', 1024);
    }
}

// Timezone — Nepal Standard Time (UTC+5:45)
date_default_timezone_set('Asia/Kathmandu');

// Character encoding
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// ─────────────────────────────────────────────────
// 2. PATH DEFINITIONS
// ─────────────────────────────────────────────────

if (!defined('BASEDIR')) {
    define('BASEDIR', __DIR__);
}

if (!defined('INCLUDES_DIR')) {
    define('INCLUDES_DIR', BASEDIR . '/includes');
}

if (!defined('CONFIG_DIR')) {
    define('CONFIG_DIR', BASEDIR . '/config');
}

if (!defined('ADMIN_DIR')) {
    define('ADMIN_DIR', BASEDIR . '/admin');
}

if (!defined('MEMBER_DIR')) {
    define('MEMBER_DIR', BASEDIR . '/member');
}

if (!defined('ASSETS_DIR')) {
    define('ASSETS_DIR', BASEDIR . '/assets');
}

if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', ASSETS_DIR . '/uploads');
}

if (!defined('DATABASE_DIR')) {
    define('DATABASE_DIR', BASEDIR . '/database');
}

// ─────────────────────────────────────────────────
// 3. DATABASE CONNECTION
// ─────────────────────────────────────────────────

// Check if database config exists
$dbConfigFile = INCLUDES_DIR . '/database.local.php';

if (file_exists($dbConfigFile)) {
    require_once $dbConfigFile;
} else {
    // Database not configured yet — individual pages handle this gracefully.
    // ENVIRONMENT already defined at top of this file.
    if (basename($_SERVER['PHP_SELF']) !== 'install.php' &&
        basename($_SERVER['PHP_SELF']) !== 'setup.php') {
        // No fatal error here; installer will configure the DB.
    }
}

// ─────────────────────────────────────────────────
// 4. LOAD CORE UTILITIES
// ─────────────────────────────────────────────────

// Define CORE_DIR if not already defined
if (!defined('CORE_DIR')) {
    define('CORE_DIR', BASEDIR . '/core');
}

// Helper functions - check both locations for compatibility
$helperFiles = [
    CORE_DIR . '/helpers.php',
    INCLUDES_DIR . '/helpers.php'
];

foreach ($helperFiles as $helperFile) {
    if (file_exists($helperFile)) {
        require_once $helperFile;
        break;
    }
}

// Authentication
$authFiles = [
    CORE_DIR . '/auth.php',
    INCLUDES_DIR . '/auth.php'
];

foreach ($authFiles as $authFile) {
    if (file_exists($authFile)) {
        require_once $authFile;
        break;
    }
}

// Validation
$validationFiles = [
    CORE_DIR . '/validation.php',
    INCLUDES_DIR . '/validation.php'
];

foreach ($validationFiles as $validationFile) {
    if (file_exists($validationFile)) {
        require_once $validationFile;
        break;
    }
}

// Config (contains requireAdminLogin and other auth functions)
$configFiles = [
    BASEDIR . '/includes/config.php',
    BASEDIR . '/config/config.php',
];

foreach ($configFiles as $configFile) {
    if (file_exists($configFile)) {
        require_once $configFile;
        break;
    }
}

// Member auth (contains requireMemberLogin)
$memberAuthFiles = [
    BASEDIR . '/includes/member-auth.php',
];

foreach ($memberAuthFiles as $memberAuthFile) {
    if (file_exists($memberAuthFile)) {
        require_once $memberAuthFile;
        break;
    }
}

// ─────────────────────────────────────────────────
// 5. SITE CONFIGURATION
// ─────────────────────────────────────────────────

// Base URL
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $protocol . $host);
}

// Site root
if (!defined('SITE_ROOT')) {
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    define('SITE_ROOT', ($scriptPath === '/') ? '/' : $scriptPath . '/');
}

// ─────────────────────────────────────────────────
// 6. SESSION HANDLING
// ─────────────────────────────────────────────────

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session options
    $_isSecure = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off' && (string)$_SERVER['HTTPS'] !== '0')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');

    @ini_set('session.use_trans_sid', '0');
    @ini_set('session.cookie_secure', $_isSecure ? '1' : '0');
    @ini_set('session.cookie_samesite', 'Lax');

    $sessionOptions = [
        'use_strict_mode' => 1,
        'use_only_cookies' => 1,
        'cookie_httponly' => 1,
        'cookie_secure' => $_isSecure,
        'cookie_samesite' => 'Lax',
        'sid_length' => 48,
        'sid_bits_per_character' => 6,
    ];
    
    session_start($sessionOptions);
}

// Regenerate session ID for security on certain pages
if (!isset($_SESSION['session_created'])) {
    $_SESSION['session_created'] = time();
}

// ─────────────────────────────────────────────────
// 7. ERROR LOGGING
// ─────────────────────────────────────────────────

if (!function_exists('log_error')) {
    function log_error($message, $level = 'ERROR') {
        $logFile = BASEDIR . '/logs/error.log';
        
        // Ensure logs directory exists
        if (!is_dir(dirname($logFile))) {
            @mkdir(dirname($logFile), 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
        
        @error_log($logEntry, 3, $logFile);
    }
}

// ─────────────────────────────────────────────────
// 8. GLOBAL FUNCTIONS
// ─────────────────────────────────────────────────

if (!function_exists('get_site_setting')) {
    /**
     * Get site setting — unified lookup.
     * Delegates to getSetting() (site_settings table, static-cached) when available.
     * Falls back to legacy 'settings' table (key/value schema) for backward compat.
     */
    function get_site_setting($key, $default = null) {
        // Primary: use getSetting() which queries site_settings + has static cache
        if (function_exists('getSetting')) {
            $val = getSetting($key, null);
            if ($val !== null) return $val;
        }

        // Legacy fallback: settings table (different schema — key/value columns)
        static $__legacy = [];
        if (array_key_exists($key, $__legacy)) {
            return $__legacy[$key] ?? $default;
        }
        try {
            $db = function_exists('getDB') ? getDB() : ($GLOBALS['pdo'] ?? null);
            if ($db) {
                $stmt = $db->prepare('SELECT `value` FROM settings WHERE `key` = ? LIMIT 1');
                $stmt->execute([$key]);
                $row = $stmt->fetch();
                $__legacy[$key] = $row ? $row['value'] : null;
                return $__legacy[$key] ?? $default;
            }
        } catch (\Exception $e) { /* ignore */ }

        return $default;
    }
}

if (!function_exists('set_site_setting')) {
    /**
     * Set site setting in database
     */
    function set_site_setting($key, $value) {
        global $pdo;
        
        try {
            if (isset($pdo)) {
                $stmt = $pdo->prepare(
                    'INSERT INTO settings (`key`, `value`, updated_at) 
                     VALUES (?, ?, NOW())
                     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = NOW()'
                );
                $stmt->execute([$key, $value]);
                
                // Clear cache
                if (isset($_SESSION['site_settings'][$key])) {
                    unset($_SESSION['site_settings'][$key]);
                }
                
                return true;
            }
        } catch (Exception $e) {
            log_error("Error setting '{$key}': " . $e->getMessage());
        }
        
        return false;
    }
}

// ─────────────────────────────────────────────────
// 9. CUSTOM ERROR HANDLER
// ─────────────────────────────────────────────────

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    log_error("PHP Error: {$errstr} in {$errfile} on line {$errline}", 'PHP');
    
    // Don't execute PHP internal error handler
    return true;
});

set_exception_handler(function($exception) {
    log_error("Exception: " . $exception->getMessage(), 'EXCEPTION');
    
    // Show error page or message
    if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') {
        throw $exception;
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        include BASEDIR . '/500.php';
        exit;
    }
});

// ─────────────────────────────────────────────────
// 10. SECURITY HEADERS
// ─────────────────────────────────────────────────

// Prevent clickjacking
header('X-Frame-Options: SAMEORIGIN');

// Prevent MIME sniffing
header('X-Content-Type-Options: nosniff');

// Enable XSS protection
header('X-XSS-Protection: 1; mode=block');

// Referrer policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// ═════════════════════════════════════════════════
// BOOTSTRAP COMPLETE
// ═════════════════════════════════════════════════
?>
