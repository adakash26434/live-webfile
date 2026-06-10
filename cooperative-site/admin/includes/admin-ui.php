<?php
/**
 * admin/includes/admin-ui.php
 * ══════════════════════════════════════════════════════════════
 * Universal Admin UI Helper Functions — v2.0
 * Enhanced: better badges, buttons, headers, empty state, etc.
 *
 * USAGE (admin-header.php pachi include garnus):
 *   require_once 'includes/admin-ui.php';
 *
 * AVAILABLE FUNCTIONS:
 *   adminPageHeader()       — page title + subtitle + right buttons
 *   adminAlert()            — success/error/warning/info alerts
 *   adminEmptyRow()         — empty state for table tbody
 *   adminBadge()            — soft pill badges
 *   adminActiveBadge()      — active/hidden badge with dot
 *   adminStatusBadge()      — pending/approved/rejected status
 *   adminStatLink()         — clickable stat badge in header
 *   adminAddBtn()           — "+ Add" button
 *   adminToggleBtn()        — active/inactive toggle form
 *   adminDeleteBtn()        — delete confirm form
 *   adminEditBtn()          — edit button (modal or page link)
 *   adminViewBtn()          — view detail button
 *   adminActionBtns()       — grouped edit+toggle+delete buttons
 *   adminSectionCard()      — grouped form section with header
 *   adminTableCard()        — standard table wrapper card
 *   adminBackBtn()          — back to list button
 *   adminFiscalYearSelect() — BS fiscal year <select>
 * ══════════════════════════════════════════════════════════════
 */
if (!defined('IS_ADMIN_PAGE')) { http_response_code(403); exit('Access denied.'); }

if (!function_exists('adminUiT')) {
    function adminUiT(string $np, string $en): string {
        static $isEn = null;
        if ($isEn === null) {
            $isEn = function_exists('isEnglish') ? (bool)isEnglish() : false;
        }
        return $isEn ? $en : $np;
    }
}

/* ──────────────────────────────────────────────────────────────
   adminPageHeader
   Page header: left = icon + title + subtitle, right = buttons
   CSS Enhancement Layer ले gradient + left-border थप्छ
   ────────────────────────────────────────────────────────────── */
function adminPageHeader(string $title, string $icon = 'fa-cog', string $subtitle = '', string $rightHtml = '', string $color = 'primary'): string {
    /* subtitle — italic muted text */
    $sub = $subtitle
        ? '<small class="text-muted d-block mt-1 admin-page-subtitle-text">'
          . htmlspecialchars($subtitle) . '</small>'
        : '';

    /* icon background circle */
    $iconHtml = '<span class="admin-page-header-icon flex-shrink-0"><i class="fas ' . $icon . '"></i></span>';

    /* Title topbar मा पहिले नै देखिन्छ — यहाँ subtitle + icon मात्र राख्ने */
    $subBlock = $sub
        ? '<div class="d-flex align-items-center gap-2 flex-shrink-0" style="min-width:0;">' . $iconHtml . $sub . '</div>'
        : '';

    /* Subtitle र right content दुवै छैन भने पूरै block नदेखाउने */
    if (!$subBlock && !$rightHtml) return '';

    return '<div class="admin-page-header mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">'
         . $subBlock
         . '<div class="d-flex gap-2 flex-wrap align-items-center">' . $rightHtml . '</div>'
         . '</div>';
}

/* ──────────────────────────────────────────────────────────────
   adminAlert
   Bootstrap alert box — modern border-left style
   ────────────────────────────────────────────────────────────── */
function adminAlert(string $type, string $msg, bool $dismiss = true): string {
    if (empty(trim($msg))) return '';
    $icons = [
        'success' => 'fa-circle-check',
        'danger'  => 'fa-circle-xmark',
        'warning' => 'fa-triangle-exclamation',
        'info'    => 'fa-circle-info'
    ];
    $icon     = $icons[$type] ?? 'fa-circle-info';
    $closeBtn = $dismiss
        ? '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="' . htmlspecialchars(adminUiT('बन्द', 'Close')) . '"></button>'
        : '';
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
         . '<i class="fas ' . $icon . ' fa-fw flex-shrink-0"></i>'
         . '<span>' . htmlspecialchars($msg) . '</span>'
         . $closeBtn . '</div>';
}

/* ──────────────────────────────────────────────────────────────
   adminEmptyRow
   Beautiful empty state <tr> for table tbody
   $icon: FA icon class without 'fa-' prefix (default: 'inbox')
   ────────────────────────────────────────────────────────────── */
function adminEmptyRow(int $colspan = 6, string $msg = '', string $sub = '', string $icon = 'inbox'): string {
    if ($msg === '') $msg = adminUiT('कुनै डाटा उपलब्ध छैन।', 'No data available.');
    if ($sub === '') $sub = adminUiT('माथिको बटनबाट नयाँ थप्नुहोस्।', 'Use the button above to add new records.');
    return '<tr class="admin-empty-row"><td colspan="' . $colspan . '" class="admin-empty-state" data-testid="admin-empty-state">'
         . '<i class="fas fa-' . htmlspecialchars($icon, ENT_QUOTES) . ' admin-empty-icon" aria-hidden="true"></i>'
         . '<div class="admin-empty-msg">' . htmlspecialchars($msg) . '</div>'
         . '<p class="admin-empty-sub">' . htmlspecialchars($sub) . '</p>'
         . '</td></tr>';
}

/* ──────────────────────────────────────────────────────────────
   adminBadge
   Soft pill badge — consistent color system
   ────────────────────────────────────────────────────────────── */
function adminBadge(string $color, string $text, bool $dark = false): string {
    /* Map Bootstrap color names → soft pill styles */
    $softStyles = [
        'success'   => 'background:color-mix(in srgb, var(--primary-color) 14%, #ffffff);color:var(--primary-dark,var(--primary-color));',
        'danger'    => 'background:color-mix(in srgb, var(--secondary-color) 16%, #ffffff);color:var(--secondary-dark,var(--secondary-color));',
        'warning'   => 'background:color-mix(in srgb, var(--secondary-color) 14%, #ffffff);color:var(--secondary-dark,var(--secondary-color));',
        'info'      => 'background:color-mix(in srgb, var(--accent-color,#17a2b8) 14%, #ffffff);color:var(--accent-color,#17a2b8);',
        'primary'   => 'background:color-mix(in srgb, var(--primary-color) 14%, #ffffff);color:var(--primary-dark,var(--primary-color));',
        'secondary' => 'background:color-mix(in srgb, var(--secondary-color) 10%, #ffffff);color:var(--secondary-dark,var(--secondary-color));',
        'dark'      => 'background:var(--primary-dark,var(--primary-color));color:var(--text-on-primary,#fff);',
        'light'     => 'background:#f9fafb;color:#374151;border:1px solid #e5e7eb;',
    ];
    $style = $softStyles[$color] ?? ('background:color-mix(in srgb, var(--primary-color) 10%, #ffffff);color:var(--primary-dark,var(--primary-color));');
    return '<span class="badge" style="'
         . 'border-radius:20px;padding:4px 10px;font-weight:600;font-size:0.72rem;'
         . $style . '">'
         . htmlspecialchars($text) . '</span>';
}

/* ──────────────────────────────────────────────────────────────
   adminActiveBadge
   Active/Hidden badge with dot indicator
   ────────────────────────────────────────────────────────────── */
function adminActiveBadge($isActive): string {
    if ($isActive) {
        return '<span class="badge" style="'
             . 'background:color-mix(in srgb, var(--primary-color) 14%, #ffffff);color:var(--primary-dark,var(--primary-color));border-radius:20px;'
             . 'padding:4px 10px;font-weight:600;font-size:0.72rem;'
             . 'display:inline-flex;align-items:center;gap:5px;">'
             . '<span style="width:6px;height:6px;border-radius:50%;background:var(--primary-color);'
             . 'box-shadow:0 0 0 2px color-mix(in srgb, var(--primary-color) 25%, transparent);flex-shrink:0;"></span>'
             . htmlspecialchars(adminUiT('सक्रिय', 'Active')) . '</span>';
    }
    return '<span class="badge" style="'
         . 'background:color-mix(in srgb, var(--secondary-color) 10%, #ffffff);color:var(--secondary-dark,var(--secondary-color));border-radius:20px;'
         . 'padding:4px 10px;font-weight:600;font-size:0.72rem;'
         . 'display:inline-flex;align-items:center;gap:5px;">'
         . '<span style="width:6px;height:6px;border-radius:50%;background:var(--text-light);flex-shrink:0;"></span>'
         . htmlspecialchars(adminUiT('लुकाइएको', 'Hidden')) . '</span>';
}

/* ──────────────────────────────────────────────────────────────
   adminStatusBadge
   Pending / Approved / Rejected / Processing status
   ────────────────────────────────────────────────────────────── */
function adminStatusBadge(string $status): string {
    $map = [
        'pending'    => ['bg' => 'color-mix(in srgb, var(--secondary-color) 14%, #ffffff)', 'color' => 'var(--secondary-dark,var(--secondary-color))', 'icon' => 'fa-clock',       'label' => 'प्रतीक्षारत'],
        'approved'   => ['bg' => 'color-mix(in srgb, var(--primary-color) 14%, #ffffff)', 'color' => 'var(--primary-dark,var(--primary-color))', 'icon' => 'fa-circle-check', 'label' => 'स्वीकृत'],
        'rejected'   => ['bg' => 'color-mix(in srgb, var(--secondary-color) 16%, #ffffff)', 'color' => 'var(--secondary-dark,var(--secondary-color))', 'icon' => 'fa-circle-xmark', 'label' => 'अस्वीकृत'],
        'processing' => ['bg' => 'color-mix(in srgb, var(--accent-color,#17a2b8) 14%, #ffffff)', 'color' => 'var(--accent-color,#17a2b8)', 'icon' => 'fa-spinner',      'label' => 'प्रक्रियामा'],
        'resolved'   => ['bg' => 'color-mix(in srgb, var(--primary-color) 14%, #ffffff)', 'color' => 'var(--primary-dark,var(--primary-color))', 'icon' => 'fa-check',        'label' => 'समाधान'],
        'closed'     => ['bg' => 'color-mix(in srgb, var(--secondary-color) 10%, #ffffff)', 'color' => 'var(--secondary-dark,var(--secondary-color))', 'icon' => 'fa-xmark',        'label' => 'बन्द'],
        'active'     => ['bg' => 'color-mix(in srgb, var(--primary-color) 14%, #ffffff)', 'color' => 'var(--primary-dark,var(--primary-color))', 'icon' => 'fa-circle',       'label' => 'Active'],
        'inactive'   => ['bg' => 'color-mix(in srgb, var(--secondary-color) 10%, #ffffff)', 'color' => 'var(--secondary-dark,var(--secondary-color))', 'icon' => 'fa-circle',       'label' => 'Inactive'],
    ];
    $s = $map[strtolower($status)] ?? ['bg' => 'color-mix(in srgb, var(--primary-color) 10%, #ffffff)', 'color' => 'var(--primary-dark,var(--primary-color))', 'icon' => 'fa-circle', 'label' => $status];
    return '<span class="badge" style="'
         . 'background:' . $s['bg'] . ';color:' . $s['color'] . ';'
         . 'border-radius:20px;padding:4px 10px;font-weight:600;font-size:0.72rem;'
         . 'display:inline-flex;align-items:center;gap:4px;">'
         . '<i class="fas ' . $s['icon'] . ' fa-fw" style="font-size:0.65rem;"></i>'
         . htmlspecialchars($s['label']) . '</span>';
}

/* ──────────────────────────────────────────────────────────────
   adminStatLink
   Clickable stat/count badge (for page header rightHtml)
   ────────────────────────────────────────────────────────────── */
function adminStatLink(string $url, string $color, string $label, $count, bool $dark = false): string {
    $softBg  = ['success' => '#dcfce7', 'danger' => '#fee2e2', 'warning' => '#fef9c3',
                 'info' => '#fef2f2', 'primary' => '#dcfce7', 'secondary' => '#f3f4f6'];
    $softClr = ['success' => '#166534', 'danger' => '#991b1b', 'warning' => '#713f12',
                 'info' => 'var(--secondary-dark,#922b21)', 'primary' => '#166534', 'secondary' => '#374151'];
    $bg  = $softBg[$color]  ?? '#f3f4f6';
    $clr = $softClr[$color] ?? '#374151';
    return '<a href="' . htmlspecialchars($url) . '" class="text-decoration-none" style="'
         . 'display:inline-flex;align-items:center;gap:5px;'
         . 'background:' . $bg . ';color:' . $clr . ';'
         . 'border-radius:20px;padding:6px 14px;font-size:0.82rem;font-weight:600;'
         . 'border:1px solid rgba(0,0,0,0.06);'
         . 'transition:all 0.15s;box-shadow:0 1px 4px rgba(0,0,0,0.05);'
         . '">' . htmlspecialchars($label) . ': <strong>' . (int)$count . '</strong></a>';
}

/* ──────────────────────────────────────────────────────────────
   adminAddBtn
   Primary "+ Add" button with gradient
   ────────────────────────────────────────────────────────────── */
function adminAddBtn(string $label, string $href = '#', string $icon = 'fa-plus', string $onclick = ''): string {
    $onclickAttr = $onclick ? ' onclick="' . htmlspecialchars($onclick, ENT_QUOTES) . '"' : '';
    if ($href !== '#') {
        return '<a href="' . htmlspecialchars($href) . '" class="btn btn-primary"' . $onclickAttr . '>'
             . '<i class="fas ' . $icon . '"></i>' . htmlspecialchars($label) . '</a>';
    }
    return '<button type="button" class="btn btn-primary"' . $onclickAttr . '>'
         . '<i class="fas ' . $icon . '"></i>' . htmlspecialchars($label) . '</button>';
}

/* ──────────────────────────────────────────────────────────────
   adminToggleBtn
   Active/Inactive compact toggle button
   ────────────────────────────────────────────────────────────── */
function adminToggleBtn(int $recordId, $isActive, string $csrfToken, string $extraFields = ''): string {
    if ($isActive) {
        $btn = '<button type="submit" class="btn btn-sm btn-success" '
             . 'title="' . htmlspecialchars(adminUiT('सक्रिय छ — थिच्दा लुकाइन्छ', 'Currently active — click to hide')) . '" '
             . 'style="min-width:82px;">'
             . '<i class="fas fa-eye fa-fw"></i> ' . htmlspecialchars(adminUiT('सक्रिय', 'Active')) . '</button>';
    } else {
        $btn = '<button type="submit" class="btn btn-sm btn-outline-secondary" '
             . 'title="' . htmlspecialchars(adminUiT('लुकाइएको छ — थिच्दा सक्रिय हुन्छ', 'Currently hidden — click to activate')) . '" '
             . 'style="min-width:82px;">'
             . '<i class="fas fa-eye-slash fa-fw"></i> ' . htmlspecialchars(adminUiT('लुकाइएको', 'Hidden')) . '</button>';
    }
    return '<form method="POST" class="d-inline">'
         . '<input type="hidden" name="action" value="toggle">'
         . '<input type="hidden" name="id" value="' . $recordId . '">'
         . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">'
         . $extraFields . $btn . '</form>';
}

/* ──────────────────────────────────────────────────────────────
   adminDeleteBtn
   Compact icon-only delete button with confirm
   ────────────────────────────────────────────────────────────── */
function adminDeleteBtn(int $recordId, string $csrfToken, string $confirmMsg = 'यो record हटाउने? यो कार्य फिर्ता हुँदैन।', string $extraFields = ''): string {
    $safeMsg = addslashes($confirmMsg);
    return '<form method="POST" class="d-inline" data-confirm="' . $safeMsg . '">'
         . '<input type="hidden" name="action" value="delete">'
         . '<input type="hidden" name="id" value="' . $recordId . '">'
         . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">'
         . $extraFields
         . '<button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="हटाउनुहोस्">'
         . '<i class="fas fa-trash-can"></i></button></form>';
}

/* ──────────────────────────────────────────────────────────────
   adminEditBtn
   Compact icon+text edit button
   ────────────────────────────────────────────────────────────── */
function adminEditBtn(string $onclick = '', string $href = '#'): string {
    if ($onclick) {
        return '<button type="button" class="btn btn-sm btn-outline-primary" '
             . 'onclick="' . htmlspecialchars($onclick, ENT_QUOTES) . '" '
             . 'title="सम्पादन" style="min-width:34px;">'
             . '<i class="fas fa-pen fa-fw"></i></button>';
    }
    return '<a href="' . htmlspecialchars($href) . '" class="btn btn-sm btn-outline-primary" '
         . 'title="सम्पादन" style="min-width:34px;">'
         . '<i class="fas fa-pen fa-fw"></i></a>';
}

/* ──────────────────────────────────────────────────────────────
   adminViewBtn
   View detail button
   ────────────────────────────────────────────────────────────── */
function adminViewBtn(string $href, string $label = ''): string {
    $txt   = $label ? ' ' . htmlspecialchars($label) : '';
    return '<a href="' . htmlspecialchars($href) . '" class="btn btn-sm btn-outline-info" '
         . 'title="हेर्नुहोस्" style="min-width:34px;">'
         . '<i class="fas fa-eye fa-fw"></i>' . $txt . '</a>';
}

/* ──────────────────────────────────────────────────────────────
   adminActionBtns
   Compact grouped action buttons (edit + toggle + delete)
   Usage: echo adminActionBtns($id, $isActive, $csrfToken, editOnclick: 'openEdit(3)');
   ────────────────────────────────────────────────────────────── */
function adminActionBtns(
    int    $recordId,
           $isActive,
    string $csrfToken,
    string $editOnclick   = '',
    string $editHref      = '#',
    bool   $showToggle    = true,
    bool   $showDelete    = true,
    string $extraToggle   = '',
    string $extraDelete   = '',
    string $confirmMsg    = 'यो record हटाउने? यो कार्य फिर्ता हुँदैन।'
): string {
    $out = '<div class="d-flex align-items-center admin-action-group" style="gap:6px;flex-wrap:wrap;">';
    $out .= adminEditBtn($editOnclick, $editHref);
    if ($showToggle) $out .= adminToggleBtn($recordId, $isActive, $csrfToken, $extraToggle);
    if ($showDelete) $out .= adminDeleteBtn($recordId, $csrfToken, $confirmMsg, $extraDelete);
    $out .= '</div>';
    return $out;
}

/* ──────────────────────────────────────────────────────────────
   adminBackBtn
   Back to list button
   ────────────────────────────────────────────────────────────── */
function adminBackBtn(string $href, string $label = 'सूचीमा फर्किनुहोस्'): string {
    return '<a href="' . htmlspecialchars($href) . '" class="btn btn-outline-secondary btn-sm">'
         . '<i class="fas fa-arrow-left me-1"></i>' . htmlspecialchars($label) . '</a>';
}

/* ──────────────────────────────────────────────────────────────
   adminSectionCard
   Grouped form section card with colored left-border header
   ────────────────────────────────────────────────────────────── */
function adminSectionCard(string $title, string $icon, string $color, string $body): string {
    /* bg + text color with !important so they beat the global .card-header gradient */
    $styles = [
        'primary'   => ['bg'=>'background:linear-gradient(135deg,rgba(26,95,42,0.07),rgba(40,167,69,0.05))!important;border-left:4px solid var(--primary-color)!important;',  'txt'=>'color:var(--primary-dark,#1a5f2a)!important;'],
        'success'   => ['bg'=>'background:rgba(34,197,94,0.06)!important;border-left:4px solid #22c55e!important;',                                                           'txt'=>'color:#166534!important;'],
        'info'      => ['bg'=>'background:rgba(14,165,233,0.05)!important;border-left:4px solid #0ea5e9!important;',                                                          'txt'=>'color:#075985!important;'],
        'warning'   => ['bg'=>'background:rgba(245,158,11,0.06)!important;border-left:4px solid #f59e0b!important;',                                                          'txt'=>'color:#92400e!important;'],
        'danger'    => ['bg'=>'background:rgba(239,68,68,0.05)!important;border-left:4px solid #ef4444!important;',                                                           'txt'=>'color:#991b1b!important;'],
        'secondary' => ['bg'=>'background:rgba(107,114,128,0.05)!important;border-left:4px solid #9ca3af!important;',                                                         'txt'=>'color:#374151!important;'],
    ];
    $s = $styles[$color] ?? $styles['primary'];
    $hdrStyle = $s['bg'] . $s['txt'];

    return '<div class="card mb-3 admin-section-card">'
         . '<div class="card-header py-2 px-3" style="' . $hdrStyle . '">'
         . '<h6 class="mb-0" style="' . $s['txt'] . 'display:flex!important;align-items:center!important;gap:8px!important;margin:0!important;">'
         . '<i class="fas ' . $icon . '"></i>' . htmlspecialchars($title) . '</h6></div>'
         . '<div class="card-body p-3">' . $body . '</div></div>';
}

/* ──────────────────────────────────────────────────────────────
   adminTableCard
   Standard card wrapper for table — optional gradient header
   ────────────────────────────────────────────────────────────── */
function adminTableCard(string $tableHtml, bool $noPad = true, string $headerTitle = '', string $headerIcon = '', string $headerRight = ''): string {
    $bodyClass = $noPad ? 'card-body p-0' : 'card-body';

    /* Optional gradient header */
    $headerHtml = '';
    if ($headerTitle) {
        $iconHtml = $headerIcon ? '<i class="fas ' . $headerIcon . ' me-2"></i>' : '';
        $rightHtml = $headerRight ? '<div>' . $headerRight . '</div>' : '';
        $headerHtml = '<div class="card-header d-flex align-items-center justify-content-between">'
                    . '<h5 class="mb-0">'
                    . $iconHtml . htmlspecialchars($headerTitle) . '</h5>'
                    . $rightHtml . '</div>';
    }

    return '<div class="card admin-table-card">'
         . $headerHtml
         . '<div class="' . $bodyClass . '"><div class="table-responsive">'
         . $tableHtml . '</div></div></div>';
}

/* ──────────────────────────────────────────────────────────────
   adminPartitionRowsByIsActive / adminListSubtabPills
   सूची कार्ड भित्र सक्रिय / अभिलेख उप-ट्याब + admin-table-subtab-content
   ────────────────────────────────────────────────────────────── */
/**
 * @param array<int,array<string,mixed>> $rows
 * @return array{live: array<int,array<string,mixed>>, archived: array<int,array<string,mixed>>}
 */
function adminPartitionRowsByIsActive(array $rows): array {
    $live = [];
    $archived = [];
    foreach ($rows as $r) {
        if (!empty($r['is_active'])) {
            $live[] = $r;
        } else {
            $archived[] = $r;
        }
    }
    return ['live' => $live, 'archived' => $archived];
}

/** उप-ट्याब पिल्स — tab-content मा class "admin-table-subtab-content" थप्नुहोस् */
function adminListSubtabPills(string $panePrefix, int $liveCount, int $archCount, ?string $liveLabel = null, ?string $archLabel = null): string {
    $p = htmlspecialchars($panePrefix, ENT_QUOTES, 'UTF-8');
    $liveLbl = htmlspecialchars($liveLabel ?? 'सक्रिय', ENT_QUOTES, 'UTF-8');
    $archLbl = htmlspecialchars($archLabel ?? 'अभिलेख', ENT_QUOTES, 'UTF-8');
    return '<ul class="nav nav-pills admin-inner-tabstrip flex-wrap gap-2 px-3 py-2 mx-3 mt-2 mb-2" role="tablist">'
        . '<li class="nav-item" role="presentation">'
        . '<button class="nav-link active py-2" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#' . $p . '-live" aria-controls="' . $p . '-live" aria-selected="true">'
        . '<i class="fas fa-bolt me-1"></i>' . $liveLbl . ' <span class="badge adm-subpill-count adm-subpill-count--live ms-1">' . (int) $liveCount . '</span></button></li>'
        . '<li class="nav-item" role="presentation">'
        . '<button class="nav-link py-2" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#' . $p . '-arch" aria-controls="' . $p . '-arch" aria-selected="false">'
        . '<i class="fas fa-archive me-1"></i>' . $archLbl . ' <span class="badge adm-subpill-count adm-subpill-count--arch ms-1">' . (int) $archCount . '</span></button></li>'
        . '</ul>';
}

/**
 * Server-side सूची: सक्रिय / अभिलेख = GET लिंक (pagination/search सँग मेल)
 *
 * @param array<string, string|int|float> $preserveQuery खोज/फिल्टर — खाली मान हटाउनुहोस्
 */
function adminListSubtabQueryLinks(
    string $panePrefix,
    int $liveCount,
    int $archCount,
    string $subParam,
    string $current,
    string $basePath,
    array $preserveQuery
): string {
    if (!preg_match('/^[a-z][a-z0-9_]*$/i', $subParam)) {
        $subParam = 'sub';
    }
    $current = ($current === 'arch') ? 'arch' : 'live';
    $basePath = preg_replace('#^\./#', '', $basePath);
    $basePath = htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8');

    $mk = static function (string $sub) use ($preserveQuery, $subParam, $basePath): string {
        $q = [];
        foreach ($preserveQuery as $k => $v) {
            if ($v === null || $v === '' || $k === 'page') {
                continue;
            }
            $q[$k] = $v;
        }
        $q[$subParam] = $sub;
        $q['page'] = 1;
        $qs = http_build_query($q);
        return $basePath . ($qs !== '' ? '?' . $qs : '');
    };

    $liveActive = $current === 'live' ? ' active' : '';
    $archActive = $current === 'arch' ? ' active' : '';
    $paneAttr = ' data-subtab-pane="' . htmlspecialchars($panePrefix, ENT_QUOTES, 'UTF-8') . '"';
    return '<ul class="nav nav-pills admin-inner-tabstrip flex-wrap gap-2 px-3 py-2 mx-3 mt-2 mb-2" role="tablist"' . $paneAttr . '>'
        . '<li class="nav-item" role="presentation"><a class="nav-link py-2' . $liveActive . '" href="' . htmlspecialchars($mk('live'), ENT_QUOTES, 'UTF-8') . '">'
        . '<i class="fas fa-bolt me-1"></i>सक्रिय <span class="badge adm-subpill-count adm-subpill-count--live ms-1">' . (int) $liveCount . '</span></a></li>'
        . '<li class="nav-item" role="presentation"><a class="nav-link py-2' . $archActive . '" href="' . htmlspecialchars($mk('arch'), ENT_QUOTES, 'UTF-8') . '">'
        . '<i class="fas fa-archive me-1"></i>अभिलेख <span class="badge adm-subpill-count adm-subpill-count--arch ms-1">' . (int) $archCount . '</span></a></li>'
        . '</ul>';
}

/* ──────────────────────────────────────────────────────────────
   adminFiscalYearSelect
   Nepali fiscal year <select> dropdown (2070/71 - 2095/96)
   ────────────────────────────────────────────────────────────── */
function adminFiscalYearSelect(string $name, string $selected = '', bool $required = false, string $id = '', string $cssClass = 'form-select'): string {
    $req   = $required ? ' required' : '';
    $idAt  = $id ? ' id="' . htmlspecialchars($id) . '"' : '';
    $opts  = '<option value="">-- आर्थिक वर्ष छान्नुहोस् --</option>';
    for ($yr = 2095; $yr >= 2070; $yr--) {
        $next = $yr + 1;
        $val  = $yr . '/' . sprintf('%02d', $next % 100);
        $sel  = ($val === $selected) ? ' selected' : '';
        $opts .= '<option value="' . $val . '"' . $sel . '>' . $val . '</option>';
    }
    return '<select name="' . htmlspecialchars($name) . '"' . $idAt
         . ' class="' . $cssClass . '"' . $req . '>' . $opts . '</select>';
}

/* ──────────────────────────────────────────────────────────────
   adminHelpTip
   Non-developer को लागि simple guidance box
   Usage: echo adminHelpTip('यो पृष्ठबाट संस्थाका सूचनाहरू थप्न सकिन्छ।', ['सूचना थप्न: "+ नयाँ सूचना" बटन थिच्नुस्।']);
   ────────────────────────────────────────────────────────────── */
function adminHelpTip(string $mainText, array $steps = [], string $icon = 'fa-circle-info'): string {
    // UX cleanup: helper tips are kept in code but hidden from admin UI.
    return '';

    $stepsHtml = '';
    if ($steps) {
        $stepsHtml = '<ul style="margin:6px 0 0 0;padding-left:18px;">';
        foreach ($steps as $s) {
            $stepsHtml .= '<li>' . htmlspecialchars($s) . '</li>';
        }
        $stepsHtml .= '</ul>';
    }
    return '<div class="admin-help-tip mb-3">'
         . '<span class="help-icon"><i class="fas ' . htmlspecialchars($icon) . '"></i></span>'
         . '<div><span>' . htmlspecialchars($mainText) . '</span>' . $stepsHtml . '</div>'
         . '</div>';
}

/* ──────────────────────────────────────────────────────────────
   adminPagination
   Bootstrap pagination nav — sliding window of pages.
   $params: array of extra query-string key→value pairs to preserve.
   $pageParam: the GET key for current page (default 'page').
   ────────────────────────────────────────────────────────────── */
function adminPagination(
    int $page,
    int $totalPages,
    int $total,
    int $limit,
    array $params = [],
    int $window = 2,
    string $pageParam = 'page'
): string {
    if ($totalPages <= 1) return '';

    $offset = ($page - 1) * $limit;
    $shown  = min($offset + $limit, $total);

    /* Build query string helper */
    $qs = static function (int $pg) use ($params, $pageParam): string {
        $p = $params;
        $p[$pageParam] = $pg;
        return '?' . http_build_query(array_filter($p, static fn($v) => $v !== null && $v !== ''));
    };

    $np = adminUiT('पछिल्लो', 'Next');
    $pp = adminUiT('अघिल्लो', 'Prev');

    $html = '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-2 border-top">'
          . '<div class="text-muted small">'
          . adminUiT("जम्मा {$total} मध्ये {$shown} देखाइएको", "Showing {$shown} of {$total}")
          . '</div>'
          . '<nav aria-label="' . adminUiT('पृष्ठ', 'Page') . '">'
          . '<ul class="pagination pagination-sm mb-0 flex-wrap gap-1">';

    /* Prev */
    $html .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">'
           . '<a class="page-link" href="' . ($page > 1 ? htmlspecialchars($qs($page - 1), ENT_QUOTES) : '#') . '">'
           . '<i class="fas fa-chevron-left" aria-hidden="true"></i><span class="visually-hidden">' . $pp . '</span></a></li>';

    /* Window */
    $start = max(1, $page - $window);
    $end   = min($totalPages, $page + $window);
    if ($start > 1)          $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
    for ($i = $start; $i <= $end; $i++) {
        $html .= '<li class="page-item ' . ($i === $page ? 'active' : '') . '">'
               . '<a class="page-link" href="' . htmlspecialchars($qs($i), ENT_QUOTES) . '">' . $i . '</a></li>';
    }
    if ($end < $totalPages)  $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';

    /* Next */
    $html .= '<li class="page-item ' . ($page >= $totalPages ? 'disabled' : '') . '">'
           . '<a class="page-link" href="' . ($page < $totalPages ? htmlspecialchars($qs($page + 1), ENT_QUOTES) : '#') . '">'
           . '<i class="fas fa-chevron-right" aria-hidden="true"></i><span class="visually-hidden">' . $np . '</span></a></li>';

    $html .= '</ul></nav></div>';
    return $html;
}

/* ──────────────────────────────────────────────────────────────
   adminQuickStat
   Simple stat pill for dashboard/header areas
   ────────────────────────────────────────────────────────────── */
function adminQuickStat(string $label, int|string $value, string $icon = 'fa-circle', string $color = 'primary'): string {
    $colors = [
        'primary' => 'background:#d1fae5;color:#065f46;',
        'danger'  => 'background:#fee2e2;color:#dc2626;',
        'warning' => 'background:#fef9c3;color:#a16207;',
        'info'    => 'background:#fef2f2;color:var(--secondary-dark,#922b21);',
    ];
    $style = $colors[$color] ?? $colors['primary'];
    return '<span style="display:inline-flex;align-items:center;gap:6px;'
         . $style . 'padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:600;">'
         . '<i class="fas ' . htmlspecialchars($icon) . '" style="font-size:.7rem;"></i>'
         . htmlspecialchars((string)$value) . ' ' . htmlspecialchars($label) . '</span>';
}

