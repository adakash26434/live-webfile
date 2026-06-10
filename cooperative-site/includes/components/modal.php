<?php
/**
 * ════════════════════════════════════════════════════════════
 * MODAL COMPONENT — Reusable Bootstrap 5 Modal Wrapper
 * ════════════════════════════════════════════════════════════
 *
 * USAGE (open):
 *   <?php
 *   $modalId      = 'deleteItemModal';       // required, unique
 *   $modalTitle   = 'रेकर्ड मेटाउनुहोस्';   // required
 *   $modalIcon    = 'fa-trash';              // optional FA icon
 *   $modalSize    = '';                      // '' | 'modal-sm' | 'modal-lg' | 'modal-xl'
 *   $modalScroll  = false;                  // modal-dialog-scrollable
 *   $modalCenter  = true;                   // modal-dialog-centered (default true)
 *   $modalStatic  = false;                  // static backdrop (prevent close on bg click)
 *   $modalHeaderClass = 'bg-danger text-white'; // optional header CSS class
 *   include __DIR__ . '/../includes/components/modal.php';
 *   ?>
 *   <!-- modal body content here -->
 *   <?php include __DIR__ . '/../includes/components/modal-close.php'; ?>
 *
 *   Then add trigger button:
 *   <button data-bs-toggle="modal" data-bs-target="#deleteItemModal">…</button>
 *
 * Constraint: PHP backend logic नछुने।
 * ════════════════════════════════════════════════════════════
 */

if (!isset($modalId))          $modalId          = 'coopModal' . uniqid();
if (!isset($modalTitle))       $modalTitle       = '';
if (!isset($modalIcon))        $modalIcon        = '';
if (!isset($modalSize))        $modalSize        = '';
if (!isset($modalScroll))      $modalScroll      = false;
if (!isset($modalCenter))      $modalCenter      = true;
if (!isset($modalStatic))      $modalStatic      = false;
if (!isset($modalHeaderClass)) $modalHeaderClass = '';

$_dialogClasses  = 'modal-dialog';
if ($modalSize)   $_dialogClasses .= ' ' . htmlspecialchars($modalSize, ENT_QUOTES);
if ($modalCenter) $_dialogClasses .= ' modal-dialog-centered';
if ($modalScroll) $_dialogClasses .= ' modal-dialog-scrollable';

$_backdrops = $modalStatic ? ' data-bs-backdrop="static" data-bs-keyboard="false"' : '';
?>
<div class="modal fade"
     id="<?php echo htmlspecialchars($modalId, ENT_QUOTES); ?>"
     tabindex="-1"
     aria-labelledby="<?php echo htmlspecialchars($modalId, ENT_QUOTES); ?>Label"
     aria-hidden="true"<?php echo $_backdrops; ?>>
  <div class="<?php echo $_dialogClasses; ?>">
    <div class="modal-content border-0 shadow">
      <div class="modal-header<?php echo $modalHeaderClass ? ' ' . htmlspecialchars($modalHeaderClass, ENT_QUOTES) : ''; ?>">
        <h5 class="modal-title" id="<?php echo htmlspecialchars($modalId, ENT_QUOTES); ?>Label">
          <?php if (!empty($modalIcon)): ?>
            <i class="fas <?php echo htmlspecialchars($modalIcon, ENT_QUOTES); ?> me-2"></i>
          <?php endif; ?>
          <?php echo htmlspecialchars($modalTitle, ENT_QUOTES, 'UTF-8'); ?>
        </h5>
        <button type="button"
                class="btn-close<?php echo ($modalHeaderClass && str_contains($modalHeaderClass, 'text-white')) ? ' btn-close-white' : ''; ?>"
                data-bs-dismiss="modal"
                aria-label="बन्द गर्नुहोस्"></button>
      </div>
      <div class="modal-body">
<?php
$GLOBALS['__modal_open_id'] = $modalId;
unset($modalId, $modalTitle, $modalIcon, $modalSize, $modalScroll, $modalCenter,
      $modalStatic, $modalHeaderClass, $_dialogClasses, $_backdrops);
?>
