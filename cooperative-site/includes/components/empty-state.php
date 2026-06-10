<?php
/**
 * ════════════════════════════════════════════════════════════
 * EMPTY STATE — Universal "No Records" Component
 * ════════════════════════════════════════════════════════════
 *
 * Use गर्ने तरिका:
 *   <?php
 *   $emptyIcon    = 'fa-inbox';         // FA icon (default: fa-inbox)
 *   $emptyTitle   = 'कुनै रेकर्ड छैन';  // heading
 *   $emptyMessage = 'थप जानकारीको लागि…'; // sub-text (optional)
 *   $emptyAction  = ['label'=>'नयाँ थप्नुहोस्','url'=>'add.php','icon'=>'fa-plus']; // optional CTA
 *   include __DIR__ . '/../includes/components/empty-state.php';
 *   ?>
 *
 * Styles: .empty-state-icon / .empty-state-title / .empty-state-body
 *         defined in global-theme.php / app-admin.css
 * ════════════════════════════════════════════════════════════
 */
if (!isset($emptyIcon))    $emptyIcon    = 'fa-inbox';
if (!isset($emptyTitle))   $emptyTitle   = 'कुनै रेकर्ड फेला परेन।';
if (!isset($emptyMessage)) $emptyMessage = '';
if (!isset($emptyAction))  $emptyAction  = [];
?>
<div class="empty-state py-5 text-center" data-testid="empty-state-container">
    <div class="mb-3">
        <span class="empty-state-icon">
            <i class="fas <?php echo htmlspecialchars($emptyIcon, ENT_QUOTES); ?> empty-state-icon-inner"></i>
        </span>
    </div>
    <h5 class="empty-state-title">
        <?php echo htmlspecialchars($emptyTitle, ENT_QUOTES, 'UTF-8'); ?>
    </h5>
    <?php if (!empty($emptyMessage)): ?>
    <p class="empty-state-body">
        <?php echo htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8'); ?>
    </p>
    <?php endif; ?>
    <?php if (!empty($emptyAction) && !empty($emptyAction['url'])): ?>
    <a href="<?php echo htmlspecialchars($emptyAction['url'], ENT_QUOTES, 'UTF-8'); ?>"
       class="btn btn-primary btn-sm" data-testid="empty-state-cta">
        <?php if (!empty($emptyAction['icon'])): ?>
            <i class="fas <?php echo htmlspecialchars($emptyAction['icon'], ENT_QUOTES); ?> me-1"></i>
        <?php endif; ?>
        <?php echo htmlspecialchars($emptyAction['label'] ?? 'थप्नुहोस्', ENT_QUOTES, 'UTF-8'); ?>
    </a>
    <?php endif; ?>
</div>
<?php unset($emptyIcon, $emptyTitle, $emptyMessage, $emptyAction); ?>
