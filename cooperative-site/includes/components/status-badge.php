<?php
/**
 * ════════════════════════════════════════════════════════════
 * STATUS BADGE — Universal Status Chip Component
 * Admin, Member, Public तीनैमा consistent badge
 * ════════════════════════════════════════════════════════════
 *
 * USAGE (function call — include once, then call anywhere):
 *   include __DIR__ . '/../includes/components/status-badge.php';
 *   echo statusBadge('pending');
 *   echo statusBadge('approved');
 *   echo statusBadge('rejected');
 *
 * Supported statuses:
 *   pending, processing, approved, rejected, disbursed,
 *   incomplete, partial, active, inactive, resolved, closed,
 *   completed, cancelled, verified, unverified
 * ════════════════════════════════════════════════════════════
 */

if (!function_exists('statusBadge')) {
    function statusBadge(string $status, bool $showIcon = true): string {
        static $map = [
            'pending'    => ['bg' => '#fef9c3', 'color' => '#713f12', 'icon' => 'fa-clock',        'label' => 'प्रतीक्षारत'],
            'processing' => ['bg' => '#e0f2fe', 'color' => '#0369a1', 'icon' => 'fa-spinner',      'label' => 'प्रक्रियामा'],
            'approved'   => ['bg' => '#dcfce7', 'color' => '#166534', 'icon' => 'fa-circle-check', 'label' => 'स्वीकृत'],
            'rejected'   => ['bg' => '#fee2e2', 'color' => '#991b1b', 'icon' => 'fa-circle-xmark', 'label' => 'अस्वीकृत'],
            'disbursed'  => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => 'fa-money-bill',   'label' => 'वितरित'],
            'incomplete' => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'fa-triangle-exclamation', 'label' => 'अपूर्ण'],
            'partial'    => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'fa-circle-half-stroke',   'label' => 'आंशिक'],
            'active'     => ['bg' => '#dcfce7', 'color' => '#166534', 'icon' => 'fa-circle',        'label' => 'सक्रिय'],
            'inactive'   => ['bg' => '#f3f4f6', 'color' => '#6b7280', 'icon' => 'fa-circle',        'label' => 'निष्क्रिय'],
            'resolved'   => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => 'fa-check',         'label' => 'समाधान'],
            'closed'     => ['bg' => '#f3f4f6', 'color' => '#6b7280', 'icon' => 'fa-xmark',         'label' => 'बन्द'],
            'completed'  => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => 'fa-check-double',  'label' => 'सम्पन्न'],
            'cancelled'  => ['bg' => '#fee2e2', 'color' => '#991b1b', 'icon' => 'fa-ban',           'label' => 'रद्द'],
            'verified'   => ['bg' => '#dcfce7', 'color' => '#166534', 'icon' => 'fa-shield-check',  'label' => 'प्रमाणित'],
            'unverified' => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'fa-shield-halved', 'label' => 'अप्रमाणित'],
            'under_review'=> ['bg'=> '#e0f2fe', 'color' => '#0369a1', 'icon' => 'fa-magnifying-glass','label'=> 'समीक्षामा'],
        ];
        $s = $map[strtolower($status)] ?? ['bg' => '#f3f4f6', 'color' => '#6b7280', 'icon' => 'fa-circle', 'label' => $status];
        $icon = $showIcon ? '<i class="fas ' . htmlspecialchars($s['icon'], ENT_QUOTES) . ' fa-fw" style="font-size:0.65em;flex-shrink:0;"></i>' : '';
        return '<span class="status-chip" data-status="' . htmlspecialchars(strtolower($status), ENT_QUOTES) . '" style="'
             . 'display:inline-flex;align-items:center;gap:4px;'
             . 'background:' . $s['bg'] . ';color:' . $s['color'] . ';'
             . 'border-radius:20px;padding:3px 10px;font-size:0.75rem;font-weight:600;'
             . 'white-space:nowrap;min-height:22px;" data-testid="status-badge-' . htmlspecialchars(strtolower($status), ENT_QUOTES) . '">'
             . $icon
             . htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8')
             . '</span>';
    }
}
?>
