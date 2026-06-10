<?php
/**
 * Public Page: संस्थागत प्रोफाइल
 * File: institutional-profile.php
 *
 * Admin मा थपिएको financial data यहाँ public page मा देखाइन्छ।
 * Admin: admin/institutional-profile.php बाट manage गर्नुहोस्।
 */
require_once 'includes/config.php';
$pageTitle = isEnglish() ? 'Institutional Profile' : 'संस्थागत प्रोफाइल';
require_once 'includes/header.php';
$L = getLangStrings();

/* ─── Fetch active institutional profiles ─── */
$profiles = [];
$tableExists = false;
try {
    $db = getDB();
    $r = $db->query("SHOW TABLES LIKE 'institutional_profile'");
    $tableExists = ($r->rowCount() > 0);
    if ($tableExists) {
        $profiles = $db->query(
            "SELECT id, fiscal_year, total_members, share_capital, deposit, loan, total_assets, other_fund, bank_cash_balance, fixed_assets, total_loan_members, npa_percent, profit_loss, is_active, created_at, updated_at FROM institutional_profile WHERE is_active = 1 ORDER BY fiscal_year DESC LIMIT 10"
        )->fetchAll();
    }
} catch (Exception $e) {
    $profiles = [];
}

/* Helper: short amount display */
function ipShortAmt(float $v): string {
    if ($v >= 1e7) return 'रू. ' . number_format($v / 1e7, 2) . ' करोड';
    if ($v >= 1e5) return 'रू. ' . number_format($v / 1e5, 1) . ' लाख';
    if ($v > 0)    return 'रू. ' . number_format($v);
    return '—';
}

function ipNepaliNumber(int $number): string {
    return strtr((string)$number, ['0'=>'०','1'=>'१','2'=>'२','3'=>'३','4'=>'४','5'=>'५','6'=>'६','7'=>'७','8'=>'८','9'=>'९']);
}
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1><?php echo isEnglish() ? 'Institutional Profile' : 'संस्थागत प्रोफाइल'; ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>"><?php echo $L['home'] ?? 'गृहपृष्ठ'; ?></a></li>
                <li class="breadcrumb-item active"><?php echo isEnglish() ? 'Institutional Profile' : 'संस्थागत प्रोफाइल'; ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Main Content -->
<section class="section-padding institutional-profile-page">
<div class="container">

<?php if (empty($profiles)): ?>
<!-- No data yet -->
<div class="text-center py-5">
    <div class="ip-empty-icon-wrap">
        <i class="fas fa-building-columns fa-2x" style="color:var(--primary-color);"></i>
    </div>
    <h4 style="color:var(--primary-color);">संस्थागत प्रोफाइल उपलब्ध छैन</h4>
    <p class="text-muted">छिट्टै उपलब्ध हुनेछ।</p>
</div>

<?php else: ?>

<!-- Section intro -->
<div class="ip-section-intro text-center mb-4">
    <span class="ip-section-kicker"><i class="fas fa-chart-line"></i> आर्थिक तथ्याङ्क</span>
    <h2>संस्थाको आर्थिक प्रोफाइल</h2>
    <p>
        वार्षिक आर्थिक तथ्याङ्क — सदस्य संख्या, शेयर, बचत, ऋण र कुल सम्पत्तिको विवरण
    </p>
</div>

<div class="ip-profile-card mb-3 ip-month-card">
    <div class="ip-card-header">
        <div class="ip-card-title-wrap">
            <div class="ip-fy-badge"><i class="fas fa-table me-2"></i> महिनागत आर्थिक विवरण</div>
            <div class="ip-date-info"><span>मुख्य आर्थिक सूचकहरू</span></div>
        </div>
    </div>

    <div class="ip-month-grid" data-testid="institutional-profile-month-wise-grid">
        <?php foreach ($profiles as $idx => $p): ?>
        <?php
            $rowNo = $idx + 1;
            $totalLoanMembers = (int)($p['total_loan_members'] ?? 0);
            $otherFund = (float)($p['other_fund'] ?? 0);
            $bankCashBalance = (float)($p['bank_cash_balance'] ?? 0);
            $fixedAssets = (float)($p['fixed_assets'] ?? 0);
            $_ipDocUrl  = !empty($p['attachment_path']) ? htmlspecialchars(SITE_URL . ltrim($p['attachment_path'], '/'), ENT_QUOTES, 'UTF-8') : '';
            $_ipDocExt  = !empty($p['attachment_path']) ? strtolower(pathinfo($p['attachment_path'], PATHINFO_EXTENSION)) : '';
        ?>
        <article class="ip-month-tile" data-testid="institutional-profile-month-card-<?php echo $rowNo; ?>">
            <div class="ip-month-tile-head">
                <div>
                    <strong data-testid="institutional-profile-fiscal-year-<?php echo $rowNo; ?>">आ.व. <?php echo htmlspecialchars($p['fiscal_year']); ?></strong>
                    <?php if (!empty($p['report_date_bs'])): ?>
                    <span data-testid="institutional-profile-published-date-<?php echo $rowNo; ?>"><?php echo htmlspecialchars($p['report_date_bs']); ?><?php if (!empty($p['report_date_ad'])): ?> / <?php echo date('d M Y', strtotime($p['report_date_ad'])); ?><?php endif; ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($p['attachment_path'])): ?>
                <button type="button" class="ip-row-doc-btn"
                        onclick="ipOpenDoc('<?php echo $_ipDocUrl; ?>','<?php echo $_ipDocExt; ?>')"
                        data-testid="institutional-profile-document-button-<?php echo $rowNo; ?>"
                        title="कागजात हेर्नुहोस्">
                    <i class="fas <?php echo $_ipDocExt === 'pdf' ? 'fa-file-pdf' : 'fa-file-image'; ?>"></i>
                </button>
                <?php endif; ?>
            </div>

            <div class="ip-month-ledger">
                <div class="ip-month-ledger-row">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(1); ?></span>
                    <span class="ip-month-title"><i class="fas fa-users"></i> कुल सदस्य</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-total-members-value-<?php echo $rowNo; ?>"><?php echo number_format((int)$p['total_members']); ?></strong><?php if (!empty($p['total_balance_member'])): ?><em><?php echo number_format((int)$p['total_balance_member']); ?> शेष</em><?php endif; ?></span>
                </div>
                <div class="ip-month-ledger-row">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(2); ?></span>
                    <span class="ip-month-title"><i class="fas fa-coins"></i> शेयर पूँजी</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-share-capital-value-<?php echo $rowNo; ?>"><?php echo ipShortAmt((float)$p['share_capital']); ?></strong><?php if (!empty($p['share_capital_percent'])): ?><em><?php echo htmlspecialchars((string)$p['share_capital_percent']); ?>% वृद्धि</em><?php endif; ?></span>
                </div>
                <div class="ip-month-ledger-row">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(3); ?></span>
                    <span class="ip-month-title"><i class="fas fa-shield-halved"></i> जगेडा कोष</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-reserved-fund-value-<?php echo $rowNo; ?>"><?php echo ipShortAmt((float)($p['reserved_fund'] ?? 0)); ?></strong><?php if (!empty($p['reserved_fund_percent'])): ?><em><?php echo htmlspecialchars((string)$p['reserved_fund_percent']); ?>% वृद्धि</em><?php endif; ?></span>
                </div>
                <div class="ip-month-ledger-row">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(4); ?></span>
                    <span class="ip-month-title"><i class="fas fa-layer-group"></i> अन्य कोष</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-other-fund-value-<?php echo $rowNo; ?>"><?php echo ipShortAmt($otherFund); ?></strong></span>
                </div>
                <div class="ip-month-ledger-row">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(5); ?></span>
                    <span class="ip-month-title"><i class="fas fa-piggy-bank"></i> कुल बचत</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-deposit-value-<?php echo $rowNo; ?>"><?php echo ipShortAmt((float)$p['deposit']); ?></strong><?php if (!empty($p['deposit_percent'])): ?><em><?php echo htmlspecialchars((string)$p['deposit_percent']); ?>% वृद्धि</em><?php endif; ?></span>
                </div>
                <div class="ip-month-ledger-row">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(6); ?></span>
                    <span class="ip-month-title"><i class="fas fa-hand-holding-dollar"></i> ऋण लगानी</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-loan-value-<?php echo $rowNo; ?>"><?php echo ipShortAmt((float)$p['loan']); ?></strong><?php if ($totalLoanMembers > 0): ?><em><?php echo number_format($totalLoanMembers); ?> ऋणी सदस्य</em><?php endif; ?></span>
                </div>
                <div class="ip-month-ledger-row">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(7); ?></span>
                    <span class="ip-month-title"><i class="fas fa-money-bill-transfer"></i> बैंक तथा नगद</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-bank-cash-balance-value-<?php echo $rowNo; ?>"><?php echo ipShortAmt($bankCashBalance); ?></strong></span>
                </div>
                <div class="ip-month-ledger-row">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(8); ?></span>
                    <span class="ip-month-title"><i class="fas fa-building-columns"></i> स्थिर सम्पत्ति</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-fixed-assets-value-<?php echo $rowNo; ?>"><?php echo ipShortAmt($fixedAssets); ?></strong></span>
                </div>
                <div class="ip-month-ledger-row ip-month-total">
                    <span class="ip-month-sn"><?php echo ipNepaliNumber(9); ?></span>
                    <span class="ip-month-title"><i class="fas fa-landmark"></i> कुल सम्पत्ति</span>
                    <span class="ip-month-value"><strong data-testid="institutional-profile-total-assets-value-<?php echo $rowNo; ?>"><?php echo ipShortAmt((float)$p['total_assets']); ?></strong></span>
                </div>
            </div>

            <div class="ip-month-tile-foot">
                <?php if (!empty($p['npa_percent'])): ?><span class="ip-mini-chip">NPA <?php echo (float)$p['npa_percent']; ?>%</span><?php endif; ?>
                <?php if (!empty($p['npl_percent'])): ?><span class="ip-mini-chip">NPL <?php echo (float)$p['npl_percent']; ?>%</span><?php endif; ?>
                <?php if (!empty($p['liquidity_percent'])): ?><span class="ip-mini-chip">Liq <?php echo (float)$p['liquidity_percent']; ?>%</span><?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div><!-- .ip-profile-card -->

<?php endif; /* end profiles check */ ?>

</div><!-- .container -->
</section>


<!-- ══════════════════════════════════════════════════════════
     Document Preview Modal — PDF / Image popup
     ══════════════════════════════════════════════════════════ -->
<style>
#ipDocModal          { display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:99999; align-items:center; justify-content:center; padding:16px; }
#ipDocModal .ip-doc-dialog { background:var(--bg-card,#fff); border-radius:14px; width:100%; max-width:920px; max-height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:var(--shadow-lg,0 24px 64px rgba(0,0,0,.4)); }
#ipDocModal .ip-doc-header { padding:14px 18px; border-bottom:1px solid var(--border-color,#e5e7eb); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-shrink:0; }
#ipDocModal .ip-doc-icon   { width:36px; height:36px; border-radius:8px; background:var(--bg-soft,#f0fdf4); display:inline-flex; align-items:center; justify-content:center; color:var(--primary-color,#1a5f2a); font-size:1.1rem; flex-shrink:0; }
#ipDocTitle                { font-weight:700; color:var(--text-primary,#1a2e1d); font-size:.95rem; }
#ipDocSub                  { font-size:.75rem; color:var(--text-muted,#6b7280); margin-top:1px; }
.ip-doc-header-actions     { display:flex; align-items:center; gap:8px; }
#ipDocDlBtn                { width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:var(--bg-soft,#f0fdf4); color:var(--color-success,#166534); text-decoration:none; border:1px solid var(--color-success-border,#bbf7d0); transition:background .15s; }
.ip-doc-close-btn          { width:36px; height:36px; border-radius:8px; border:none; background:var(--bg-muted,#f3f4f6); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; color:var(--text-muted,#6b7280); transition:background .15s; }
#ipDocBody                 { flex:1; overflow:auto; min-height:420px; display:flex; align-items:center; justify-content:center; background:var(--bg-soft,#f9fafb); }
#ipDocLoader               { text-align:center; padding:40px; color:var(--text-muted,#9ca3af); }
</style>

<div id="ipDocModal" data-testid="institutional-profile-document-modal" onclick="if(event.target===this)ipCloseDoc()">
  <div class="ip-doc-dialog">

    <!-- Header -->
    <div class="ip-doc-header">
      <div style="display:flex;align-items:center;gap:10px;">
        <span id="ipDocIcon" class="ip-doc-icon"></span>
        <div>
          <div id="ipDocTitle"></div>
          <div id="ipDocSub"></div>
        </div>
      </div>
      <div class="ip-doc-header-actions">
        <a id="ipDocDlBtn" href="#" download target="_blank"
           title="<?php echo isEnglish() ? 'Download' : 'डाउनलोड'; ?>"
           data-testid="institutional-profile-document-download-link">
          <i class="fas fa-download" style="font-size:.85rem;"></i>
        </a>
        <button onclick="ipCloseDoc()" class="ip-doc-close-btn"
                title="<?php echo isEnglish() ? 'Close' : 'बन्द'; ?>"
                data-testid="institutional-profile-document-close-button">
          <i class="fas fa-xmark" style="font-size:1rem;"></i>
        </button>
      </div>
    </div>

    <!-- Body -->
    <div id="ipDocBody">
      <div id="ipDocLoader">
        <i class="fas fa-spinner fa-spin fa-2x" style="margin-bottom:12px;display:block;"></i>
        <div style="font-size:.85rem;"><?php echo isEnglish() ? 'Loading…' : 'लोड हुँदैछ…'; ?></div>
      </div>
    </div>

  </div>
</div>

<script>
(function () {
    function ipOpenDoc(url, ext) {
        var modal  = document.getElementById('ipDocModal');
        var body   = document.getElementById('ipDocBody');
        var loader = document.getElementById('ipDocLoader');
        var dlBtn  = document.getElementById('ipDocDlBtn');
        var title  = document.getElementById('ipDocTitle');
        var sub    = document.getElementById('ipDocSub');
        var icon   = document.getElementById('ipDocIcon');
        var isImg  = ['jpg','jpeg','png','gif','webp'].indexOf(ext) !== -1;

        dlBtn.href = url;
        if (isImg) {
            icon.innerHTML  = '<i class="fas fa-image"></i>';
            title.textContent = '<?php echo isEnglish() ? 'Image Document' : 'छवि कागजात'; ?>';
            sub.textContent = ext.toUpperCase();
        } else {
            icon.innerHTML  = '<i class="fas fa-file-pdf"></i>';
            title.textContent = '<?php echo isEnglish() ? 'PDF Document' : 'PDF कागजात'; ?>';
            sub.textContent = 'PDF';
        }

        /* Show modal */
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        body.innerHTML = '';
        body.appendChild(loader);
        loader.style.display = 'block';

        /* Inject content */
        if (isImg) {
            var img = document.createElement('img');
            img.src = url;
            img.alt = '<?php echo isEnglish() ? 'Document Preview' : 'कागजात पूर्वावलोकन'; ?>';
            img.style.cssText = 'max-width:100%;max-height:75vh;border-radius:8px;display:block;padding:16px;';
            img.onload  = function () { loader.style.display = 'none'; body.style.justifyContent = 'center'; };
            img.onerror = function () { loader.innerHTML = '<i class="fas fa-triangle-exclamation fa-2x" style="color:#dc2626;margin-bottom:12px;display:block;"></i><div><?php echo isEnglish() ? 'Could not load image.' : 'छवि लोड भएन।'; ?></div>'; };
            body.appendChild(img);
        } else {
            var iframe = document.createElement('iframe');
            iframe.src   = url;
            iframe.title = '<?php echo isEnglish() ? 'PDF Preview' : 'PDF पूर्वावलोकन'; ?>';
            iframe.style.cssText = 'width:100%;height:75vh;border:0;display:block;';
            iframe.onload = function () { loader.style.display = 'none'; };
            body.style.justifyContent = 'flex-start';
            body.appendChild(iframe);
        }
    }

    function ipCloseDoc() {
        var modal = document.getElementById('ipDocModal');
        modal.style.display = 'none';
        document.getElementById('ipDocBody').innerHTML = '';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') ipCloseDoc();
    });

    /* Expose globally */
    window.ipOpenDoc  = ipOpenDoc;
    window.ipCloseDoc = ipCloseDoc;
}());
</script>

<?php require_once 'includes/footer.php'; ?>
