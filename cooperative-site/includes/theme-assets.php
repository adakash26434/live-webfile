<?php
/**
 * Unified theme CSS stack — Public, Admin, Member, Auth, Verify.
 * Load order: design-tokens → global-theme (DB) → panel CSS → coop → overrides v4.
 */
if (!function_exists('coopThemeCssUrl')) {

    function coopThemeCssUrl(string $rel): string
    {
        $base = defined('SITE_URL') ? SITE_URL : '/';
        return rtrim($base, '/') . '/' . ltrim($rel, '/');
    }

    function coopThemeCssVer(string $rel): string
    {
        $root = defined('ROOT_PATH') ? ROOT_PATH : (dirname(__DIR__) . '/');
        $mtime = @filemtime($root . ltrim($rel, '/'));
        return $mtime ? (string) $mtime : '1';
    }

    function coopThemeLink(string $rel, ?string $ver = null): void
    {
        $v = $ver ?? coopThemeCssVer($rel);
        $href = coopThemeCssUrl($rel) . '?v=' . rawurlencode($v);
        echo '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    /** DB brand colors — always after design-tokens.css */
    function coopThemeRequireGlobal(): void
    {
        if (!function_exists('getSetting')) {
            return;
        }
        $file = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__) . '/') . 'assets/css/global-theme.php';
        if (is_file($file)) {
            require $file;
        }
    }

    function coopThemeGoogleFonts(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link href="https://fonts.googleapis.com/css2?family=Mukta:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@300;400;500;600;700&display=swap" rel="stylesheet">' . "\n";
    }

    function coopThemeDetectPanel(): string
    {
        if (defined('PORTAL') && is_string(PORTAL) && PORTAL !== '') {
            return PORTAL;
        }
        if (defined('IS_ADMIN_PAGE') && IS_ADMIN_PAGE) {
            return 'admin';
        }
        $script = (string) ($_SERVER['PHP_SELF'] ?? '');
        if (str_contains($script, '/admin/')) {
            return 'admin';
        }
        if (str_contains($script, '/member/')) {
            return 'member';
        }
        if (str_contains($script, 'verify.php')) {
            return 'verify';
        }
        return 'public';
    }

    /**
     * @param string $panel public|admin|member|auth|verify|minimal|shell|auto
     * @param array{skip_fonts?:bool, extra?:string[]} $options
     */
    function coopThemeHeadAssets(string $panel = 'auto', array $options = []): void
    {
        static $emitted = [];
        $key = $panel . '|' . implode(',', $options['extra'] ?? []);
        if (isset($emitted[$key])) {
            return;
        }
        $emitted[$key] = true;

        if ($panel === 'auto') {
            $panel = coopThemeDetectPanel();
        }

        if (empty($options['skip_fonts'])) {
            coopThemeGoogleFonts();
        }

        /* ── 1. Load the static panel CSS FIRST ── */
        $script = (string) ($_SERVER['PHP_SELF'] ?? '');
        $isAdminShell = str_contains($script, '/admin/');

        switch ($panel) {
            case 'admin':
            case 'admin-auth':
                coopThemeLink('assets/css/app-admin.css');
                break;

            case 'member':
            case 'shell':
                coopThemeLink('assets/css/' . ($isAdminShell ? 'app-admin.css' : 'app-member.css'));
                break;

            case 'auth':
            case 'verify':
                coopThemeLink('assets/css/app-member.css');
                break;

            case 'minimal':
            case 'public':
            default:
                coopThemeLink('assets/css/app-public.css');
                break;
        }

        foreach ($options['extra'] ?? [] as $rel) {
            coopThemeLink($rel);
        }

        /* ── 2. DB-computed brand colors AFTER static CSS so !important wins ── */
        coopThemeRequireGlobal();
    }

    /** @deprecated Use coopThemeHeadAssets('auth') — kept for existing login/password pages */
    function memberHeadAssets(): void
    {
        coopThemeHeadAssets('auth', ['skip_fonts' => false]);
    }
}
