<?php
/**
 * MODAL CLOSE — closes modal.php wrapper
 *
 * Optionally accepts:
 *   $modalFooter (string) — HTML for modal-footer (e.g., cancel/submit buttons)
 *
 * USAGE:
 *   <?php
 *   $modalFooter = '
 *     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द गर्नुहोस्</button>
 *     <button type="submit" class="btn btn-primary">बुझाउनुहोस्</button>';
 *   include __DIR__ . '/../includes/components/modal-close.php';
 *   ?>
 */
if (!isset($modalFooter)) $modalFooter = '';
?>
      </div><!-- /.modal-body -->
      <?php if (!empty($modalFooter)): ?>
      <div class="modal-footer">
        <?php echo $modalFooter; ?>
      </div>
      <?php endif; ?>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php
unset($modalFooter);
unset($GLOBALS['__modal_open_id']);
?>
