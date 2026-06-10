<?php
/**
 * ════════════════════════════════════════════════════════════
 * PAGINATION — Bootstrap Sliding-Window Page Nav Component
 * Public, Member, Admin तीनैमा प्रयोग गर्न मिल्ने
 * ════════════════════════════════════════════════════════════
 *
 * USAGE:
 *   <?php
 *   $paginationPage       = $page;          // current page (1-based)
 *   $paginationTotalPages = $totalPages;    // total page count
 *   $paginationTotal      = $total;         // total record count
 *   $paginationLimit      = $limit;         // records per page
 *   $paginationParams     = ['search' => $search, 'status' => $status_filter]; // extra GET params (optional)
 *   $paginationPageParam  = 'page';         // GET key for page number (default: 'page')
 *   $paginationWindow     = 2;              // pages shown each side of current (default: 2)
 *   include __DIR__ . '/../includes/components/pagination.php';
 *   ?>
 *
 * Constraint: PHP backend logic नछुने।
 * ════════════════════════════════════════════════════════════
 */
if (!isset($paginationPage))       $paginationPage       = 1;
if (!isset($paginationTotalPages)) $paginationTotalPages = 1;
if (!isset($paginationTotal))      $paginationTotal      = 0;
if (!isset($paginationLimit))      $paginationLimit      = 15;
if (!isset($paginationParams))     $paginationParams     = [];
if (!isset($paginationPageParam))  $paginationPageParam  = 'page';
if (!isset($paginationWindow))     $paginationWindow     = 2;

if ($paginationTotalPages <= 1) {
    unset($paginationPage, $paginationTotalPages, $paginationTotal, $paginationLimit,
          $paginationParams, $paginationPageParam, $paginationWindow);
    return;
}

$_pgOffset = ($paginationPage - 1) * $paginationLimit;
$_pgShown  = min($_pgOffset + $paginationLimit, $paginationTotal);

/* query-string builder */
$_pgQs = function (int $pg) use ($paginationParams, $paginationPageParam): string {
    $p = $paginationParams;
    $p[$paginationPageParam] = $pg;
    return '?' . http_build_query(array_filter($p, static fn($v) => $v !== null && $v !== ''));
};
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3" data-testid="pagination-nav">
    <div class="text-muted small">
        जम्मा <?php echo $paginationTotal; ?> मध्ये <?php echo $_pgShown; ?> देखाइएको
    </div>
    <nav aria-label="पृष्ठ नेभिगेसन">
        <ul class="pagination pagination-sm mb-0 flex-wrap gap-1">
            <!-- Prev -->
            <li class="page-item <?php echo $paginationPage <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" <?php if ($paginationPage > 1) echo 'href="' . htmlspecialchars($_pgQs($paginationPage - 1), ENT_QUOTES) . '"'; ?>
                   aria-label="अघिल्लो">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </a>
            </li>

            <?php
            $_pgStart = max(1, $paginationPage - $paginationWindow);
            $_pgEnd   = min($paginationTotalPages, $paginationPage + $paginationWindow);
            if ($_pgStart > 1): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>

            <?php for ($_pg = $_pgStart; $_pg <= $_pgEnd; $_pg++): ?>
            <li class="page-item <?php echo $_pg === $paginationPage ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo htmlspecialchars($_pgQs($_pg), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo $_pg; ?>
                </a>
            </li>
            <?php endfor; ?>

            <?php if ($_pgEnd < $paginationTotalPages): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>

            <!-- Next -->
            <li class="page-item <?php echo $paginationPage >= $paginationTotalPages ? 'disabled' : ''; ?>">
                <a class="page-link" <?php if ($paginationPage < $paginationTotalPages) echo 'href="' . htmlspecialchars($_pgQs($paginationPage + 1), ENT_QUOTES) . '"'; ?>
                   aria-label="पछिल्लो">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>
<?php
unset($paginationPage, $paginationTotalPages, $paginationTotal, $paginationLimit,
      $paginationParams, $paginationPageParam, $paginationWindow,
      $_pgOffset, $_pgShown, $_pgQs, $_pgStart, $_pgEnd, $_pg);
?>
