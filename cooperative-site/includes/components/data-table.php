<?php
/**
 * ════════════════════════════════════════════════════════════
 * DATA TABLE — Responsive Mobile-friendly Table Component
 * ════════════════════════════════════════════════════════════
 *
 * USAGE — यसलाई wrapper को रूपमा प्रयोग गर्नुहोस्:
 *
 *   <?php
 *   $tableHeaders = ['सि.नं.', 'नाम', 'ठेगाना', 'मिति', 'कार्य'];
 *   $tableId      = 'membersTable';    // DataTables id (optional)
 *   $tableClass   = '';                // extra class (optional)
 *   $tableEmpty   = 'कुनै रेकर्ड छैन।'; // empty state message (optional)
 *   $tableSearch  = true;              // show search input (optional, default false)
 *   include __DIR__ . '/../includes/components/data-table.php';
 *   ?>
 *   <!-- tbody rows यहाँ -->
 *   <tr>
 *     <td data-label="सि.नं.">1</td>
 *     <td data-label="नाम">राम बहादुर</td>
 *     ...
 *   </tr>
 *   <?php include __DIR__ . '/../includes/components/data-table-close.php'; ?>
 *
 * Mobile card-view: data-label attribute अनिवार्य।
 * Constraint: PHP backend logic नछुने।
 * ════════════════════════════════════════════════════════════
 */
if (!isset($tableHeaders) || !is_array($tableHeaders)) $tableHeaders = [];
if (!isset($tableId))     $tableId     = '';
if (!isset($tableClass))  $tableClass  = '';
if (!isset($tableEmpty))  $tableEmpty  = 'कुनै रेकर्ड फेला परेन।';
if (!isset($tableSearch)) $tableSearch = false;

$_idAttr    = $tableId    ? " id=\"{$tableId}\""    : '';
$_classAttr = $tableClass ? " {$tableClass}"          : '';
?>
<?php if ($tableSearch): ?>
<div class="d-flex justify-content-end mb-2">
    <div class="input-group" style="max-width:280px;">
        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted" style="font-size:.82rem;"></i></span>
        <input type="text" class="form-control border-start-0 ps-0"
               placeholder="खोज्नुहोस्…"
               onkeyup="(function(v){document.querySelectorAll('#<?php echo $tableId ?: 'dataTable'; ?> tbody tr').forEach(function(r){r.style.display=r.textContent.toLowerCase().includes(v)?'':'none';});})(this.value.toLowerCase())"
               style="min-height:38px;">
    </div>
</div>
<?php endif; ?>
<div class="v9-table-scroll">
<table<?php echo $_idAttr; ?> class="table table-striped table-hover align-middle coop-table table-responsive-stack<?php echo $_classAttr; ?>">
<thead>
    <tr>
    <?php foreach ($tableHeaders as $_h): ?>
        <th><?php echo htmlspecialchars((string)$_h, ENT_QUOTES, 'UTF-8'); ?></th>
    <?php endforeach; ?>
    </tr>
</thead>
<tbody>
<?php
// Caller inserts rows here.
// data-table-close.php closes tbody/table/div.
// Store empty message for use in close component:
$GLOBALS['__dt_empty']  = $tableEmpty;
$GLOBALS['__dt_cols']   = count($tableHeaders);
unset($tableHeaders, $tableId, $tableClass, $tableEmpty, $tableSearch, $_idAttr, $_classAttr, $_h);
?>
