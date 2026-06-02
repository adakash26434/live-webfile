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
            "SELECT * FROM institutional_profile WHERE is_active = 1 ORDER BY fiscal_year DESC LIMIT 10"
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
<section class="section-padding">
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
<div class="text-center mb-5">
    <h2 style="color:var(--primary-color);font-weight:700;">संस्थाको आर्थिक प्रोफाइल</h2>
    <p class="text-muted" style="max-width:650px;margin:0 auto;">
        वार्षिक आर्थिक तथ्याङ्क — सदस्य संख्या, शेयर, बचत, ऋण र कुल सम्पत्तिको विवरण
    </p>
    <div style="width:60px;height:4px;background:linear-gradient(90deg,var(--primary-color),var(--primary-light));border-radius:2px;margin:16px auto 0;"></div>
</div>

<?php foreach ($profiles as $p): ?>
<!-- ── Profile Card for each fiscal year ── -->
<div class="ip-profile-card mb-5">

    <!-- Card Header -->
    <div class="ip-card-header">
        <div class="ip-fy-badge">
            <i class="fas fa-calendar-days me-2"></i>
            आ.व. <?php echo htmlspecialchars($p['fiscal_year']); ?>
        </div>
        <?php if (!empty($p['report_date_bs'])): ?>
        <div class="ip-date-info">
            <i class="fas fa-clock me-1"></i>
            <span style="opacity:0.82;font-size:0.88em;margin-right:4px;">प्रकाशित:</span>
            <?php echo htmlspecialchars($p['report_date_bs']); ?>
            <?php if (!empty($p['report_date_ad'])): ?>
            &nbsp;/&nbsp; <?php echo date('d M Y', strtotime($p['report_date_ad'])); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($p['attachment_path'])): ?>
        <?php
            $_ipDocUrl  = htmlspecialchars(SITE_URL . ltrim($p['attachment_path'], '/'), ENT_QUOTES, 'UTF-8');
            $_ipDocExt  = strtolower(pathinfo($p['attachment_path'], PATHINFO_EXTENSION));
            $_ipDocLbl  = isEnglish() ? 'View Document' : 'कागजात हेर्नुहोस्';
        ?>
        <button type="button" class="ip-doc-btn"
                onclick="ipOpenDoc('<?php echo $_ipDocUrl; ?>','<?php echo $_ipDocExt; ?>')"
                title="<?php echo $_ipDocLbl; ?>">
            <span class="ip-doc-btn-icon">
                <?php if ($_ipDocExt === 'pdf'): ?>
                <i class="fas fa-file-pdf"></i>
                <?php else: ?>
                <i class="fas fa-file-image"></i>
                <?php endif; ?>
            </span>
            <?php echo $_ipDocLbl; ?>
            <i class="fas fa-up-right-from-square" style="font-size:.72em;opacity:.75;"></i>
        </button>
        <?php endif; ?>
    </div>

    <!-- Stats Grid -->
    <div class="ip-stats-grid">

        <!-- Members -->
        <div class="ip-stat-item ip-stat-primary">
            <div class="ip-stat-icon"><i class="fas fa-users"></i></div>
            <div class="ip-stat-body">
                <div class="ip-stat-value"><?php echo number_format((int)$p['total_members']); ?></div>
                <div class="ip-stat-label">कुल सदस्य</div>
                <?php if (!empty($p['total_balance_member'])): ?>
                <div class="ip-stat-sub"><?php echo number_format((int)$p['total_balance_member']); ?> शेष</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Total Assets -->
        <div class="ip-stat-item ip-stat-success">
            <div class="ip-stat-icon"><i class="fas fa-landmark"></i></div>
            <div class="ip-stat-body">
                <div class="ip-stat-value"><?php echo ipShortAmt((float)$p['total_assets']); ?></div>
                <div class="ip-stat-label">कुल सम्पत्ति</div>
            </div>
        </div>

        <!-- Share Capital -->
        <div class="ip-stat-item ip-stat-info">
            <div class="ip-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="ip-stat-body">
                <div class="ip-stat-value"><?php echo ipShortAmt((float)$p['share_capital']); ?></div>
                <div class="ip-stat-label">शेयर पूँजी</div>
                <?php if (!empty($p['share_capital_percent'])): ?>
                <div class="ip-stat-sub"><?php echo $p['share_capital_percent']; ?>% वृद्धि</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Deposit/Savings -->
        <div class="ip-stat-item ip-stat-teal">
            <div class="ip-stat-icon"><i class="fas fa-piggy-bank"></i></div>
            <div class="ip-stat-body">
                <div class="ip-stat-value"><?php echo ipShortAmt((float)$p['deposit']); ?></div>
                <div class="ip-stat-label">कुल बचत</div>
                <?php if (!empty($p['deposit_percent'])): ?>
                <div class="ip-stat-sub"><?php echo $p['deposit_percent']; ?>% वृद्धि</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Loan -->
        <div class="ip-stat-item ip-stat-warning">
            <div class="ip-stat-icon"><i class="fas fa-hand-holding-dollar"></i></div>
            <div class="ip-stat-body">
                <div class="ip-stat-value"><?php echo ipShortAmt((float)$p['loan']); ?></div>
                <div class="ip-stat-label">ऋण लगानी</div>
                <?php if (!empty($p['loan_percent'])): ?>
                <div class="ip-stat-sub"><?php echo $p['loan_percent']; ?>% वृद्धि</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reserved Fund -->
        <?php if (!empty($p['reserved_fund'])): ?>
        <div class="ip-stat-item ip-stat-purple">
            <div class="ip-stat-icon"><i class="fas fa-shield-halved"></i></div>
            <div class="ip-stat-body">
                <div class="ip-stat-value"><?php echo ipShortAmt((float)$p['reserved_fund']); ?></div>
                <div class="ip-stat-label">जगेडा कोष</div>
                <?php if (!empty($p['reserved_fund_percent'])): ?>
                <div class="ip-stat-sub"><?php echo $p['reserved_fund_percent']; ?>% वृद्धि</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- .ip-stats-grid -->

    <!-- Indicators Row (NPA, NPL, Liquidity) -->
    <?php
    $hasIndicators = !empty($p['npa_percent']) || !empty($p['npl_percent']) || !empty($p['liquidity_percent']);
    if ($hasIndicators):
    ?>
    <div class="ip-indicators-row">
        <?php if (!empty($p['npa_percent'])): ?>
        <div class="ip-indicator">
            <div class="ip-ind-label">NPA (खराब ऋण)</div>
            <div class="ip-ind-bar-wrap">
                <?php
                $npa = (float)$p['npa_percent'];
                $npaClass = $npa < 3 ? 'bar-good' : ($npa < 5 ? 'bar-warning' : 'bar-danger');
                $barWidth = min($npa * 10, 100);
                ?>
                <div class="ip-ind-bar <?php echo $npaClass; ?>" style="width:<?php echo $barWidth; ?>%"></div>
            </div>
            <div class="ip-ind-value <?php echo $npaClass; ?>"><?php echo $npa; ?>%</div>
        </div>
        <?php endif; ?>

        <?php if (!empty($p['npl_percent'])): ?>
        <div class="ip-indicator">
            <div class="ip-ind-label">NPL</div>
            <div class="ip-ind-bar-wrap">
                <?php $npl = (float)$p['npl_percent']; $bw = min($npl * 10, 100); ?>
                <div class="ip-ind-bar bar-info" style="width:<?php echo $bw; ?>%"></div>
            </div>
            <div class="ip-ind-value ip-ind-value-info"><?php echo $npl; ?>%</div>
        </div>
        <?php endif; ?>

        <?php if (!empty($p['liquidity_percent'])): ?>
        <div class="ip-indicator">
            <div class="ip-ind-label">तरलता (Liquidity)</div>
            <div class="ip-ind-bar-wrap">
                <?php $liq = (float)$p['liquidity_percent']; $bw2 = min($liq, 100); ?>
                <div class="ip-ind-bar bar-teal" style="width:<?php echo $bw2; ?>%"></div>
            </div>
            <div class="ip-ind-value ip-ind-value-teal"><?php echo $liq; ?>%</div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Loan Reserve Fund -->
    <?php if (!empty($p['total_loan_reserve_fund'])): ?>
    <div class="ip-reserve-row">
        <i class="fas fa-vault me-2 text-success"></i>
        <strong>ऋण सुरक्षण कोष:</strong>
        <?php echo ipShortAmt((float)$p['total_loan_reserve_fund']); ?>
        <?php if (!empty($p['total_loan_reserve_percent'])): ?>
        <span class="ip-reserve-pct">(<?php echo $p['total_loan_reserve_percent']; ?>%)</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Note -->
    <?php if (!empty($p['report_note'])): ?>
    <div class="ip-note">
        <i class="fas fa-info-circle me-2 text-muted"></i>
        <?php echo nl2br(htmlspecialchars($p['report_note'])); ?>
    </div>
    <?php endif; ?>


</div><!-- .ip-profile-card -->
<?php endforeach; ?>

<?php endif; /* end profiles check */ ?>

</div><!-- .container -->
</section>


<!-- ══════════════════════════════════════════════════════════
     Document Preview Modal — PDF / Image popup
     ══════════════════════════════════════════════════════════ -->
<div id="ipDocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:99999;align-items:center;justify-content:center;padding:16px;" onclick="if(event.target===this)ipCloseDoc()">
  <div style="background:#fff;border-radius:14px;width:100%;max-width:920px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.4);">

    <!-- Header -->
    <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0;">
      <div style="display:flex;align-items:center;gap:10px;">
        <span id="ipDocIcon" style="width:36px;height:36px;border-radius:8px;background:#f0fdf4;display:inline-flex;align-items:center;justify-content:center;color:var(--primary-color,#1a5f2a);font-size:1.1rem;flex-shrink:0;"></span>
        <div>
          <div id="ipDocTitle" style="font-weight:700;color:#1a2e1d;font-size:.95rem;"></div>
          <div id="ipDocSub" style="font-size:.75rem;color:#6b7280;margin-top:1px;"></div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px;">
        <a id="ipDocDlBtn" href="#" download target="_blank"
           style="width:36px;height:36px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:#f0fdf4;color:#166534;text-decoration:none;border:1px solid #bbf7d0;transition:background .15s;"
           title="<?php echo isEnglish() ? 'Download' : 'डाउनलोड'; ?>">
          <i class="fas fa-download" style="font-size:.85rem;"></i>
        </a>
        <button onclick="ipCloseDoc()"
                style="width:36px;height:36px;border-radius:8px;border:none;background:#f3f4f6;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:#6b7280;transition:background .15s;"
                title="<?php echo isEnglish() ? 'Close' : 'बन्द'; ?>">
          <i class="fas fa-xmark" style="font-size:1rem;"></i>
        </button>
      </div>
    </div>

    <!-- Body -->
    <div id="ipDocBody" style="flex:1;overflow:auto;min-height:420px;display:flex;align-items:center;justify-content:center;background:#f9fafb;">
      <div id="ipDocLoader" style="text-align:center;padding:40px;color:#9ca3af;">
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
