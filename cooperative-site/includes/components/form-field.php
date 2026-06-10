<?php
/**
 * ════════════════════════════════════════════════════════════
 * FORM FIELD — Standardized Bootstrap Form Row Component
 * ════════════════════════════════════════════════════════════
 *
 * USAGE:
 *   <?php
 *   $fieldLabel    = 'पूरा नाम';             // required
 *   $fieldName     = 'full_name';             // required - HTML name attribute
 *   $fieldId       = 'full_name';             // optional (defaults to fieldName)
 *   $fieldType     = 'text';                  // text|email|tel|number|date|textarea|select|checkbox|hidden
 *   $fieldValue    = $row['full_name'] ?? ''; // pre-fill value
 *   $fieldRequired = true;                   // show required marker
 *   $fieldPlaceholder = 'नाम लेख्नुहोस्';   // optional
 *   $fieldHelp     = 'नेपालीमा लेख्नुहोस्'; // optional help text
 *   $fieldError    = '';                      // inline error message
 *   $fieldAttrs    = 'maxlength="120"';       // optional extra HTML attributes
 *   $fieldOptions  = [                        // for select type
 *     ['value'=>'active','label'=>'सक्रिय'],
 *     ['value'=>'inactive','label'=>'निष्क्रिय'],
 *   ];
 *   $fieldColMd    = 6;                       // Bootstrap col-md-* (default 12 = full row)
 *   $fieldRows     = 3;                       // textarea rows (default 3)
 *   include __DIR__ . '/../includes/components/form-field.php';
 *   ?>
 *
 * Constraint: PHP backend logic नछुने।
 * ════════════════════════════════════════════════════════════
 */

if (!isset($fieldLabel))       $fieldLabel       = '';
if (!isset($fieldName))        $fieldName        = '';
if (!isset($fieldId))          $fieldId          = $fieldName;
if (!isset($fieldType))        $fieldType        = 'text';
if (!isset($fieldValue))       $fieldValue       = '';
if (!isset($fieldRequired))    $fieldRequired    = false;
if (!isset($fieldPlaceholder)) $fieldPlaceholder = '';
if (!isset($fieldHelp))        $fieldHelp        = '';
if (!isset($fieldError))       $fieldError       = '';
if (!isset($fieldAttrs))       $fieldAttrs       = '';
if (!isset($fieldOptions))     $fieldOptions     = [];
if (!isset($fieldColMd))       $fieldColMd       = 12;
if (!isset($fieldRows))        $fieldRows        = 3;

$_colClass   = 'col-12' . ($fieldColMd < 12 ? " col-md-{$fieldColMd}" : '');
$_reqMark    = $fieldRequired ? ' <span class="text-danger" aria-hidden="true">*</span>' : '';
$_errClass   = !empty($fieldError) ? ' is-invalid' : '';
$_reqAttr    = $fieldRequired ? ' required' : '';

// Hidden fields: no wrapper
if ($fieldType === 'hidden') {
    echo '<input type="hidden" name="' . htmlspecialchars($fieldName, ENT_QUOTES) . '" id="' . htmlspecialchars($fieldId, ENT_QUOTES) . '" value="' . htmlspecialchars((string)$fieldValue, ENT_QUOTES) . '">';
    goto _ff_cleanup;
}

// Checkbox: special layout
if ($fieldType === 'checkbox'):
?>
<div class="<?php echo $_colClass; ?> mb-3">
  <div class="form-check">
    <input class="form-check-input<?php echo $_errClass; ?>"
           type="checkbox"
           name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES); ?>"
           id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES); ?>"
           value="1"
           <?php echo $fieldValue ? 'checked' : ''; ?>
           <?php echo $_reqAttr; ?>
           <?php echo $fieldAttrs; ?>>
    <label class="form-check-label" for="<?php echo htmlspecialchars($fieldId, ENT_QUOTES); ?>">
      <?php echo htmlspecialchars($fieldLabel, ENT_QUOTES, 'UTF-8'); ?><?php echo $_reqMark; ?>
    </label>
    <?php if (!empty($fieldHelp)): ?>
      <div class="form-text"><?php echo htmlspecialchars($fieldHelp, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!empty($fieldError)): ?>
      <div class="invalid-feedback"><?php echo htmlspecialchars($fieldError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
  </div>
</div>
<?php
goto _ff_cleanup;
endif;
?>
<div class="<?php echo $_colClass; ?> mb-3">
  <label class="form-label" for="<?php echo htmlspecialchars($fieldId, ENT_QUOTES); ?>">
    <?php echo htmlspecialchars($fieldLabel, ENT_QUOTES, 'UTF-8'); ?><?php echo $_reqMark; ?>
  </label>

  <?php if ($fieldType === 'select'): ?>
    <select class="form-select<?php echo $_errClass; ?>"
            name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES); ?>"
            id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES); ?>"
            <?php echo $_reqAttr; ?>
            <?php echo $fieldAttrs; ?>>
      <?php if (!empty($fieldPlaceholder)): ?>
        <option value=""><?php echo htmlspecialchars($fieldPlaceholder, ENT_QUOTES, 'UTF-8'); ?></option>
      <?php endif; ?>
      <?php foreach ($fieldOptions as $opt): ?>
        <option value="<?php echo htmlspecialchars((string)($opt['value'] ?? ''), ENT_QUOTES); ?>"
          <?php echo (string)$fieldValue === (string)($opt['value'] ?? '') ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars((string)($opt['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
        </option>
      <?php endforeach; ?>
    </select>

  <?php elseif ($fieldType === 'textarea'): ?>
    <textarea class="form-control<?php echo $_errClass; ?>"
              name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES); ?>"
              id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES); ?>"
              rows="<?php echo (int)$fieldRows; ?>"
              placeholder="<?php echo htmlspecialchars($fieldPlaceholder, ENT_QUOTES, 'UTF-8'); ?>"
              <?php echo $_reqAttr; ?>
              <?php echo $fieldAttrs; ?>><?php echo htmlspecialchars((string)$fieldValue, ENT_QUOTES, 'UTF-8'); ?></textarea>

  <?php else: ?>
    <input class="form-control<?php echo $_errClass; ?>"
           type="<?php echo htmlspecialchars($fieldType, ENT_QUOTES); ?>"
           name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES); ?>"
           id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES); ?>"
           value="<?php echo htmlspecialchars((string)$fieldValue, ENT_QUOTES, 'UTF-8'); ?>"
           placeholder="<?php echo htmlspecialchars($fieldPlaceholder, ENT_QUOTES, 'UTF-8'); ?>"
           <?php echo $_reqAttr; ?>
           <?php echo $fieldAttrs; ?>>
  <?php endif; ?>

  <?php if (!empty($fieldHelp)): ?>
    <div class="form-text"><?php echo htmlspecialchars($fieldHelp, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>
  <?php if (!empty($fieldError)): ?>
    <div class="invalid-feedback"><?php echo htmlspecialchars($fieldError, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>
</div>
<?php
_ff_cleanup:
unset($fieldLabel, $fieldName, $fieldId, $fieldType, $fieldValue, $fieldRequired,
      $fieldPlaceholder, $fieldHelp, $fieldError, $fieldAttrs, $fieldOptions,
      $fieldColMd, $fieldRows, $_colClass, $_reqMark, $_errClass, $_reqAttr);
?>
