<?php
/**
 * ════════════════════════════════════════════════════════════
 * FORM SECTION — Uniform Form Card Section Component
 * ════════════════════════════════════════════════════════════
 *
 * USAGE:
 *   <?php
 *   $formSectionTitle = 'व्यक्तिगत जानकारी';
 *   $formSectionIcon  = 'fa-user';   // optional FA icon
 *   include __DIR__ . '/../includes/components/form-section.php';
 *   ?>
 *   <!-- form fields यहाँ -->
 *   <?php include __DIR__ . '/../includes/components/form-section-close.php'; ?>
 *
 * Constraint: PHP backend logic नछुने।
 * ════════════════════════════════════════════════════════════
 */
if (!isset($formSectionTitle)) $formSectionTitle = '';
if (!isset($formSectionIcon))  $formSectionIcon  = '';
?>
<div class="form-card mb-4">
    <?php if (!empty($formSectionTitle)): ?>
    <div class="form-card-title">
        <?php if (!empty($formSectionIcon)): ?>
            <i class="fas <?php echo htmlspecialchars($formSectionIcon, ENT_QUOTES); ?>"></i>
        <?php endif; ?>
        <?php echo htmlspecialchars($formSectionTitle, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php endif; ?>
    <div class="form-section-body">
<?php
$GLOBALS['__fs_open'] = true;
unset($formSectionTitle, $formSectionIcon);
?>
