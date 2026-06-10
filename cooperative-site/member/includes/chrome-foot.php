<?php
/**
 * Member chrome footer — closes container/body/html,
 * emits mobile 4-item bottom nav + More drawer.
 *
 * Variables available from chrome.php scope:
 *   $_siteUrl, $_active, $_unread, $_electionState,
 *   $_hasIdCard, $_isEn
 * Local helpers:
 *   $_footT   — translation closure (np|en)
 */

/* Translation helper (own copy so file can be included standalone) */
$_footLang  = function_exists('getCurrentLang') ? getCurrentLang() : 'np';
$_footIsEn  = ($_footLang === 'en');
$_footT     = static function (string $np, string $en) use ($_footIsEn): string {
    return $_footIsEn ? $en : $np;
};

/* Active page key (from chrome.php or default) */
$_fnActive = isset($_active) ? $_active : '';

/* Unread count (from chrome.php or 0) */
$_fnUnread = isset($_unread) ? (int)$_unread : 0;

/* Site URL (from chrome.php or constant) */
$_fnUrl = isset($_siteUrl) ? $_siteUrl : (defined('SITE_URL') ? SITE_URL : '/');

/* Election state (from chrome.php or 'none') */
$_fnElection = isset($_electionState) ? $_electionState : 'none';

/* ID card always shown */
$_fnHasId = true;

/* ── 4 primary bottom-nav items ── */
$_fnPrimary = [
    [
        'href'   => $_fnUrl . 'member/',
        'icon'   => 'fa-house',
        'label'  => $_footT('गृह', 'Home'),
        'active' => 'dashboard',
    ],
    [
        'href'   => $_fnUrl . 'member/tracker.php',
        'icon'   => 'fa-magnifying-glass-chart',
        'label'  => $_footT('ट्र्याकर', 'Tracker'),
        'active' => 'tracker',
    ],
    [
        'href'   => $_fnUrl . 'member/notifications.php',
        'icon'   => 'fa-bell',
        'label'  => $_footT('सूचना', 'Alerts'),
        'active' => 'notifications',
        'badge'  => $_fnUnread,
    ],
    [
        'href'   => $_fnUrl . 'member/profile.php',
        'icon'   => 'fa-user-circle',
        'label'  => $_footT('प्रोफाइल', 'Profile'),
        'active' => 'profile',
    ],
];

/* ── Secondary items for the More drawer ── */
$_fnSecondary = [];
if ($_fnHasId) {
    $_fnSecondary[] = [
        'href'   => $_fnUrl . 'member/id-card.php',
        'icon'   => 'fa-id-card',
        'label'  => $_footT('परिचयपत्र', 'ID Card'),
        'active' => 'idcard',
    ];
}
$_fnSecondary[] = ['href'=>$_fnUrl.'member/welfare.php',         'icon'=>'fa-heart-pulse',       'label'=>$_footT('कल्याण दाबी','Welfare'),      'active'=>'welfare'];
if ($_fnElection === 'voting') {
    $_fnSecondary[] = ['href'=>$_fnUrl.'member/election-vote.php','icon'=>'fa-check-to-slot',    'label'=>$_footT('मतदान','Vote'),               'active'=>'election'];
} elseif ($_fnElection === 'candidates') {
    $_fnSecondary[] = ['href'=>$_fnUrl.'member/election-vote.php','icon'=>'fa-users',             'label'=>$_footT('उम्मेदवार','Candidates'),      'active'=>'election'];
}
$_fnSecondary[] = ['href'=>$_fnUrl.'member/scan.php',           'icon'=>'fa-qrcode',            'label'=>$_footT('QR स्क्यान','QR Scan'),        'active'=>'scan'];
$_fnSecondary[] = ['href'=>$_fnUrl.'member/attend.php',         'icon'=>'fa-calendar-check',    'label'=>$_footT('उपस्थिति','Attendance'),       'active'=>'attend'];
$_fnSecondary[] = ['href'=>$_fnUrl.'member/service-request.php','icon'=>'fa-concierge-bell',    'label'=>$_footT('सेवा अनुरोध','Service Req.'),  'active'=>'service'];
$_fnSecondary[] = ['href'=>$_fnUrl.'member/appointment.php',    'icon'=>'fa-calendar-plus',     'label'=>$_footT('भेटघाट','Appointment'),        'active'=>'apply-appointment'];
$_fnSecondary[] = ['href'=>$_fnUrl.'member/loan-apply.php',     'icon'=>'fa-hand-holding-dollar','label'=>$_footT('ऋण आवेदन','Loan Apply'),      'active'=>'apply-loan'];
$_fnSecondary[] = ['href'=>$_fnUrl.'member/account-apply.php',  'icon'=>'fa-landmark',          'label'=>$_footT('खाता खोल्ने','Open Account'),  'active'=>'apply-account'];
$_fnSecondary[] = ['href'=>$_fnUrl.'member/digital-service.php','icon'=>'fa-laptop',            'label'=>$_footT('डिजिटल सेवा','Digital Svc'),  'active'=>'apply-digital'];
$_fnSecondary[] = ['href'=>$_fnUrl.'member/grievance.php',      'icon'=>'fa-comment-dots',      'label'=>$_footT('गुनासो','Grievance'),          'active'=>'apply-grievance'];
$_fnSecondary[] = ['href'=>$_fnUrl.'member/certificate.php',    'icon'=>'fa-certificate',       'label'=>$_footT('प्रमाणपत्र','Certificates'),  'active'=>'certificate'];
$_fnSecondary[] = ['href'=>$_fnUrl,                              'icon'=>'fa-globe',             'label'=>$_footT('मुख्य साइट','Main Site'),      'active'=>'__mainsite',      'target'=>'_blank'];
?>
</div><!-- /.mem-container -->

<!-- ══ Mobile: 4-item bottom nav ══ -->
<nav class="mp-bottom-nav" id="mpBottomNav" aria-label="<?php echo $_footT('मुख्य नेभिगेसन','Main Navigation'); ?>">

    <?php foreach ($_fnPrimary as $_fnItem): ?>
    <a href="<?php echo htmlspecialchars($_fnItem['href']); ?>"
       class="mp-bottom-nav__item<?php echo ($_fnActive === $_fnItem['active']) ? ' active' : ''; ?>"
       aria-label="<?php echo htmlspecialchars($_fnItem['label']); ?>">
        <i class="fas <?php echo $_fnItem['icon']; ?>"></i>
        <span><?php echo htmlspecialchars($_fnItem['label']); ?></span>
        <?php if (!empty($_fnItem['badge']) && $_fnItem['badge'] > 0): ?>
        <span class="mp-bn-badge"><?php echo (int)$_fnItem['badge'] > 9 ? '9+' : (int)$_fnItem['badge']; ?></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>

    <!-- More button -->
    <button class="mp-bn-more" id="mpMoreBtn" type="button" aria-label="<?php echo $_footT('थप नेभिगेसन','More Navigation'); ?>">
        <i class="fas fa-grip"></i>
        <span><?php echo $_footT('थप', 'More'); ?></span>
    </button>

</nav>

<!-- ══ More drawer — backdrop ══ -->
<div class="mp-more-overlay" id="mpMoreOverlay" role="dialog" aria-modal="true" aria-label="<?php echo $_footT('सबै सेवाहरू','All Services'); ?>"></div>

<!-- ══ More drawer — slide-up sheet ══ -->
<div class="mp-more-drawer" id="mpMoreDrawer">
    <div class="mp-more-drag" aria-hidden="true"></div>
    <div class="mp-more-head">
        <span><i class="fas fa-grip" style="font-size:.8rem;margin-right:6px;"></i><?php echo $_footT('सबै सेवाहरू', 'All Services'); ?></span>
        <button class="mp-more-close" id="mpMoreClose" type="button" aria-label="<?php echo $_footT('बन्द गर्नुहोस्','Close'); ?>">×</button>
    </div>
    <div class="mp-more-grid">
        <?php foreach ($_fnSecondary as $_fnSec):
            $_fnIsActive = ($_fnActive === $_fnSec['active']);
        ?>
        <a href="<?php echo htmlspecialchars($_fnSec['href']); ?>"
           class="mp-more-item<?php echo $_fnIsActive ? ' active' : ''; ?>"
           <?php if (!empty($_fnSec['target'])): ?>target="<?php echo htmlspecialchars($_fnSec['target']); ?>" rel="noopener"<?php endif; ?>
           aria-label="<?php echo htmlspecialchars($_fnSec['label']); ?>">
            <span class="mp-mi-icon"><i class="fas <?php echo htmlspecialchars($_fnSec['icon']); ?>"></i></span>
            <span><?php echo htmlspecialchars($_fnSec['label']); ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="<?php echo $_fnUrl; ?>assets/js/v9-mobile-fix.js?v=9.11" defer></script>
<script>
(function () {
    /* ── Bell dropdown ── */
    var bellBtn = document.getElementById('bellBtn');
    var bellDd  = document.getElementById('bellDropdown');
    if (bellBtn && bellDd) {
        bellBtn.addEventListener('click', function (e) { e.stopPropagation(); bellDd.classList.toggle('open'); });
        document.addEventListener('click', function (e) {
            if (bellDd.contains(e.target) || e.target === bellBtn) return;
            bellDd.classList.remove('open');
        });
    }

    /* ── Bottom nav active highlight ── */
    (function () {
        var path = window.location.pathname;
        document.querySelectorAll('.mp-bottom-nav__item').forEach(function (a) {
            var href = a.getAttribute('href') || '';
            var page = href.split('/').pop().split('?')[0];
            var cur  = path.split('/').pop().split('?')[0];
            if (page && cur && (page === cur || (page === '' && (cur === '' || cur === 'index.php')))) {
                a.classList.add('active');
            }
        });
    }());

    /* ── More drawer ── */
    var moreBtn     = document.getElementById('mpMoreBtn');
    var moreOverlay = document.getElementById('mpMoreOverlay');
    var moreDrawer  = document.getElementById('mpMoreDrawer');
    var moreClose   = document.getElementById('mpMoreClose');
    if (!moreBtn || !moreOverlay || !moreDrawer) return;

    function openDrawer() {
        moreOverlay.classList.add('open');
        moreDrawer.classList.add('open');
        moreBtn.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        moreOverlay.classList.remove('open');
        moreDrawer.classList.remove('open');
        moreBtn.classList.remove('open');
        document.body.style.overflow = '';
    }

    moreBtn.addEventListener('click', function () {
        if (moreDrawer.classList.contains('open')) { closeDrawer(); } else { openDrawer(); }
    });
    moreOverlay.addEventListener('click', closeDrawer);
    if (moreClose) moreClose.addEventListener('click', closeDrawer);

    /* Swipe-down to close */
    var _startY = 0;
    moreDrawer.addEventListener('touchstart', function (e) { _startY = e.touches[0].clientY; }, { passive: true });
    moreDrawer.addEventListener('touchend',   function (e) {
        var dy = e.changedTouches[0].clientY - _startY;
        if (dy > 60) closeDrawer();
    }, { passive: true });

    /* Escape key */
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
}());
</script>

<!-- Dark Mode: sync with public site preference (member portal) -->
<script>
(function() {
    var saved = localStorage.getItem('coop_dark_mode');
    if (saved === '1' || (saved === null && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.body.classList.add('dark-mode');
    }
}());
</script>
</body>
</html>
