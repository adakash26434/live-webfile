"""Regression checks for Nepal address dataset, KYC selectors, mobile JS, and profile fields."""

from __future__ import annotations

import json
import os
import re
import subprocess
from pathlib import Path


# Auto-detect project root — works both when code lives at /app/ and /app/cooperative-site/
_env_root = os.environ.get("COOP_ROOT", "")
ROOT = Path(_env_root) if _env_root else (
    Path("/app/cooperative-site") if Path("/app/cooperative-site").is_dir() else Path("/app")
)


def run_cmd(cmd: list[str]) -> subprocess.CompletedProcess:
    return subprocess.run(cmd, cwd=ROOT, capture_output=True, text=True)


def run_php_inline(code: str) -> str:
    # Skip if php not available in environment
    import shutil
    if not shutil.which("php"):
        import pytest
        pytest.skip("php CLI not available in this environment")
    # Replace /app/ with the actual root path in inline PHP code
    adjusted = code.replace("'/app/", f"'{ROOT}/").replace('"/app/', f'"{ROOT}/')
    proc = run_cmd(["php", "-r", adjusted])
    assert proc.returncode == 0, f"PHP failed: {proc.stderr or proc.stdout}"
    return proc.stdout.strip()


def _p(rel: str) -> Path:
    """Return absolute path relative to project root."""
    return ROOT / rel.lstrip("/").replace("app/", "", 1) if rel.startswith("/app/") else ROOT / rel


def test_nepal_address_counts_and_uniqueness() -> None:
    code = r'''
require '/app/includes/nepal-address.php';
$data = getNepalAddressData();
$provinceCount = count($data);
$districtCount = 0;
$localLevelCount = 0;
$dupWithinDistrict = [];
$seenByDistrict = [];

foreach ($data as $province => $districts) {
    $districtCount += count($districts);
    foreach ($districts as $district => $municipalities) {
        $seenByDistrict[$district] = [];
        foreach ($municipalities as $m) {
            $name = trim((string)($m['name'] ?? ''));
            if ($name === '') continue;
            $localLevelCount++;
            if (isset($seenByDistrict[$district][$name])) {
                $dupWithinDistrict[] = $district . '::' . $name;
            }
            $seenByDistrict[$district][$name] = true;
        }
    }
}

echo json_encode([
    'provinces' => $provinceCount,
    'districts' => $districtCount,
    'local_levels' => $localLevelCount,
    'dup_within_district' => array_values(array_unique($dupWithinDistrict)),
], JSON_UNESCAPED_UNICODE);
'''
    out = run_php_inline(code)
    payload = json.loads(out)
    assert payload["provinces"] == 7
    assert payload["districts"] == 77
    assert payload["local_levels"] == 753
    assert payload["dup_within_district"] == []


def test_nepal_address_specific_corrected_entries() -> None:
    code = r'''
require '/app/includes/nepal-address.php';
$data = getNepalAddressData();

function findWard($data, $district, $targetName) {
  foreach ($data as $province => $districts) {
    if (!isset($districts[$district])) continue;
    foreach ($districts[$district] as $m) {
      if (($m['name'] ?? '') === $targetName) {
        return (int)($m['wards'] ?? 0);
      }
    }
  }
  return null;
}

echo json_encode([
  'dodhara_chandani' => findWard($data, 'कञ्चनपुर', 'दोधारा चाँदनी नगरपालिका'),
  'pachaljharana' => findWard($data, 'कालीकोट', 'पचालझरना गाउँपालिका'),
  'dordi' => findWard($data, 'लमजुङ', 'दोर्दी गाउँपालिका')
], JSON_UNESCAPED_UNICODE);
'''
    out = run_php_inline(code)
    payload = json.loads(out)
    assert payload["dodhara_chandani"] == 10
    assert payload["pachaljharana"] == 9
    assert payload["dordi"] == 9


def test_php_syntax_for_modified_files() -> None:
    import shutil
    if not shutil.which("php"):
        import pytest
        pytest.skip("php CLI not available in this environment")
    files = [
        "includes/nepal-address.php",
        "includes/header.php",
        "online-kyc.php",
        "admin/institutional-profile.php",
        "institutional-profile.php",
        "admin/includes/ensure-admin-tables.php",
    ]
    for f in files:
        abs_path = str(ROOT / f)
        proc = run_cmd(["php", "-l", abs_path])
        assert proc.returncode == 0, f"Syntax error in {f}: {proc.stderr or proc.stdout}"


def test_mobile_menu_js_syntax() -> None:
    js_path = str(ROOT / "assets/js/v9-mobile-fix.js")
    proc = run_cmd(["node", "--check", js_path])
    assert proc.returncode == 0, proc.stderr or proc.stdout


def test_admin_profile_new_fields_wired() -> None:
    content = (ROOT / "admin/institutional-profile.php").read_text(encoding="utf-8")
    for field in ["other_fund", "bank_cash_balance", "fixed_assets", "total_loan_members"]:
        assert f"$_POST['{field}']" in content
        assert f"name=\"{field}\"" in content
        assert f"'{field}'" in content


def test_kyc_selectors_present() -> None:
    content = (ROOT / "online-kyc.php").read_text(encoding="utf-8")
    required_ids = [
        "kyc-permanent-province-select",
        "kyc-permanent-district-select",
        "kyc-permanent-municipality-select",
        "kyc-permanent-ward-select",
        "kyc-same-as-permanent-checkbox",
    ]
    for rid in required_ids:
        assert rid in content


def test_public_profile_new_fields_are_missing_safe() -> None:
    """Should use null-safe access to avoid Undefined array key notices on old schema."""
    content = (ROOT / "institutional-profile.php").read_text(encoding="utf-8")
    expected_safe_patterns = [
        "($p['other_fund'] ??",
        "($p['bank_cash_balance'] ??",
        "($p['fixed_assets'] ??",
        "($p['total_loan_members'] ??",
    ]
    for pattern in expected_safe_patterns:
        assert pattern in content, f"Missing null-safe access for: {pattern}"


def test_btn_overflow_hidden_removed() -> None:
    """The .btn { overflow:hidden } rule was clipping Devanagari text descenders / icon bottoms.
    Must be removed from app-core.css and app-public.css."""
    for css_rel in ("assets/css/app-core.css", "assets/css/app-public.css"):
        content = (ROOT / css_rel).read_text(encoding="utf-8")
        # strip CSS comments first so we don't false-positive on removed-comment text
        no_comments = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
        for m in re.finditer(r'^\.btn\s*\{[^}]*\}', no_comments, flags=re.MULTILINE):
            body = m.group(0).replace(" ", "")
            assert "overflow:hidden" not in body, (
                f"Found overflow:hidden inside .btn block in {css_rel}: {m.group(0)[:200]}"
            )


def test_global_theme_has_final_patch() -> None:
    """global-theme.php must end with the FINAL UNIFORMITY PATCH block that fixes
    button underlines, inactive nav-tabs visibility on green strip, and bottom-nav icons."""
    content = (ROOT / "assets/css/global-theme.php").read_text(encoding="utf-8")
    assert "FINAL UNIFORMITY PATCH" in content
    assert ".admin-bottom-nav .admin-nav-item" in content
    assert ".admin-inner-tabstrip .nav-link:not(.active)" in content


def test_install_sql_no_duplicate_hrm_tables() -> None:
    """install.sql must not duplicate HRM CREATE TABLE statements."""
    content = (ROOT / "database/install.sql").read_text(encoding="utf-8")
    for tbl in (
        "hrm_departments",
        "hrm_employees",
        "hrm_employee_contracts",
        "hrm_employee_documents",
        "hrm_internal_messages",
    ):
        count = content.count(f"CREATE TABLE IF NOT EXISTS {tbl} (")
        assert count == 1, f"Table {tbl} has {count} CREATE statements, expected 1"


def test_btn_neutralizer_block_removed() -> None:
    """The harmful 'neutralize all colored buttons' block in app-admin.css must be removed."""
    content = (ROOT / "assets/css/app-admin.css").read_text(encoding="utf-8")
    bad_pattern = (".btn-success,\n"
                   ".btn-info,\n"
                   ".btn-warning,\n"
                   ".btn-secondary,\n"
                   ".btn-outline-success,\n"
                   ".btn-outline-info,\n"
                   ".btn-outline-warning,\n"
                   ".btn-outline-secondary,\n"
                   ".btn-outline-primary {\n"
                   "    background: #ffffff !important;")
    assert bad_pattern not in content, (
        "The 'neutralize buttons' block is still present in app-admin.css. "
        "It forces white background on all colored buttons and hides icons."
    )


def test_mobile_drawer_stacking_fix_present() -> None:
    """Public mobile menu drawer (#mainNavV2) stacking-context fix must be present."""
    content = (ROOT / "includes/header.php").read_text(encoding="utf-8")
    assert "body.header-v2.mobile-nav-open .pfl-header-wrapper" in content, (
        "Missing stacking-context fix for mobile drawer."
    )
    m = re.search(
        r'body\.header-v2\.mobile-nav-open\s+\.pfl-header-wrapper\s*\{[^}]*z-index:\s*(\d+)',
        content,
    )
    assert m is not None, "Stacking fix block must set explicit z-index"
    assert int(m.group(1)) > 2147483000, (
        f"Wrapper z-index ({m.group(1)}) must be > backdrop z-index (2147483000)"
    )


def test_fix_pass2_present() -> None:
    """FIX-PASS 2 block must be in global-theme.php."""
    content = (ROOT / "assets/css/global-theme.php").read_text(encoding="utf-8")
    assert "FIX-PASS 2" in content
    assert ".tools-widget-section .tools-category-card h5" in content
    assert ".btn-coop, a.btn-coop, button.btn-coop" in content
    assert "overflow: visible" in content
    assert 'button[form="profileMainForm"].btn' in content


def test_fix_pass3_global_icon_devanagari() -> None:
    """FIX-PASS 3 block must be present — global icon scaling + Devanagari descender safety."""
    content = (ROOT / "assets/css/global-theme.php").read_text(encoding="utf-8")
    assert "FIX-PASS 3" in content
    assert ".dropdown-menu .dropdown-item i" in content
    assert "font-size: 0.92em" in content
    assert "height: auto !important" in content
    assert ".badge" in content
    assert '.btn[style*="height"]' in content


def test_dark_mode_toggle_implemented() -> None:
    """Dark mode toggle must have JavaScript implementation in main.js."""
    content = (ROOT / "assets/js/main.js").read_text(encoding="utf-8")
    assert "topbarDarkModeToggle" in content, "Dark mode toggle JS must reference button ID"
    assert "dark-mode" in content, "Dark mode JS must toggle body.dark-mode class"
    assert "localStorage" in content, "Dark mode preference must persist via localStorage"
    assert "coop_dark_mode" in content, "Dark mode must use coop_dark_mode localStorage key"


def test_no_n_plus_one_show_columns_in_header() -> None:
    """header.php must not run SHOW COLUMNS FROM pages more than once per request."""
    content = (ROOT / "includes/header.php").read_text(encoding="utf-8")
    count = content.count("SHOW COLUMNS FROM pages LIKE 'show_in_menu'")
    assert count <= 1, (
        f"Found {count} SHOW COLUMNS queries — expected ≤1 (cached). "
        "Multiple SHOW COLUMNS queries cause N+1 DB overhead on every public page."
    )


# ─────────────────────────────────────────────────────────────────────────────
#  PHASE 2 — Shared Component System Regression
# ─────────────────────────────────────────────────────────────────────────────

def test_new_components_exist() -> None:
    """pagination.php and status-badge.php must exist in the components directory."""
    comp_dir = ROOT / "includes/components"
    assert (comp_dir / "pagination.php").is_file(), "pagination.php component is missing"
    assert (comp_dir / "status-badge.php").is_file(), "status-badge.php component is missing"
    assert (comp_dir / "_registry.php").is_file(), "_registry.php component index is missing"


def test_pagination_component_has_required_vars() -> None:
    """pagination.php must accept all required $pagination* variables."""
    content = (ROOT / "includes/components/pagination.php").read_text(encoding="utf-8")
    for var in ("$paginationPage", "$paginationTotalPages", "$paginationTotal",
                "$paginationLimit", "$paginationParams"):
        assert var in content, f"pagination.php is missing variable {var}"
    assert "data-testid=\"pagination-nav\"" in content, \
        "pagination.php must have data-testid for automated testing"


def test_status_badge_defines_function() -> None:
    """status-badge.php must define statusBadge() function for reuse."""
    content = (ROOT / "includes/components/status-badge.php").read_text(encoding="utf-8")
    assert "function statusBadge" in content, "statusBadge() function must be defined"
    assert "function_exists('statusBadge')" in content, \
        "statusBadge() must be wrapped with function_exists guard"
    for status in ("pending", "approved", "rejected"):
        assert status in content, f"status-badge.php must handle '{status}' status"


def test_admin_pagination_function_added() -> None:
    """adminPagination() must be defined in admin-ui.php."""
    content = (ROOT / "admin/includes/admin-ui.php").read_text(encoding="utf-8")
    assert "function adminPagination(" in content, \
        "adminPagination() function must be defined in admin-ui.php"
    assert "$pageParam" in content, "adminPagination() must support custom page parameter name"
    assert "http_build_query" in content, "adminPagination() must build query string for links"


def test_admin_empty_row_accepts_icon_param() -> None:
    """adminEmptyRow() must accept a 4th $icon parameter."""
    content = (ROOT / "admin/includes/admin-ui.php").read_text(encoding="utf-8")
    # Function signature must include $icon parameter
    assert "string $icon = 'inbox'" in content or "string $icon=" in content, \
        "adminEmptyRow() must accept $icon parameter (default 'inbox')"


def test_admin_pages_use_pagination_function() -> None:
    """Key admin pages must use adminPagination() instead of inline pagination HTML."""
    for page in ("audit-log.php", "appointments.php", "member-online-portal.php"):
        content = (ROOT / "admin" / page).read_text(encoding="utf-8")
        assert "adminPagination(" in content, \
            f"admin/{page} must use adminPagination() function"
        # Must NOT have the old inline pagination pattern
        assert '<ul class="pagination pagination-sm' not in content, \
            f"admin/{page} still has inline pagination HTML — use adminPagination() instead"


def test_empty_state_migration_completed() -> None:
    """Key admin pages must NOT have old inline empty-state <tr> HTML."""
    # Pages that were migrated — check for the specific multi-line pattern
    # that was replaced, not just icon class names (icons can appear elsewhere)
    migrated_pages = [
        ("awards.php",      "fa-trophy fa-3x mb-2 d-block opacity-25"),
        ("news.php",        "fa-newspaper fa-3x mb-2 d-block"),
        ("feedbacks.php",   "fa-inbox fa-3x mb-3 d-block"),
        ("grievances.php",  "no-results-row"),
        ("services.php",    "fa-concierge-bell fa-3x"),
    ]
    for page, pattern in migrated_pages:
        path = ROOT / "admin" / page
        if not path.is_file():
            continue
        content = path.read_text(encoding="utf-8")
        assert pattern not in content, (
            f"admin/{page} still has old inline empty-state HTML matching '{pattern}'. "
            "Use adminEmptyRow() instead."
        )


def test_registry_documents_new_components() -> None:
    """_registry.php must document pagination.php and status-badge.php."""
    content = (ROOT / "includes/components/_registry.php").read_text(encoding="utf-8")
    assert "pagination.php" in content, "_registry.php must document pagination.php"
    assert "status-badge.php" in content, "_registry.php must document status-badge.php"
    assert "statusBadge(" in content, "_registry.php must show statusBadge() usage example"


def test_csrf_added_to_hrm_and_staff_pages() -> None:
    """Staff and HRM pages must call checkCSRF() in their POST handlers."""
    pages_needing_csrf = [
        "staff.php",
        "hrm-employees.php",
        "hrm-departments.php",
        "push-notifications.php",
    ]
    for page in pages_needing_csrf:
        path = ROOT / "admin" / page
        if not path.is_file():
            continue
        content = path.read_text(encoding="utf-8")
        assert "checkCSRF()" in content, (
            f"admin/{page} must call checkCSRF() in its POST handler. "
            "CSRF verification is mandatory for all state-changing forms."
        )


def test_admin_empty_state_css_has_dark_mode() -> None:
    """admin-empty-state CSS block must include dark-mode rules for text contrast."""
    content = (ROOT / "assets/css/app-admin.css").read_text(encoding="utf-8")
    assert ".dark-mode .admin-empty-state" in content, \
        "app-admin.css must have dark-mode overrides for .admin-empty-state"
    assert ".admin-empty-icon" in content, \
        "app-admin.css must use .admin-empty-icon class (not inline styles)"


def test_no_dead_flash_calls_in_admin_pages() -> None:
    """Admin pages that include admin-header must not have redundant getFlash() calls."""
    # admin-header.php already calls getFlash() and renders it in the layout.
    # Individual pages that call it again are dead code.
    pages_cleaned = [
        "awards.php", "members.php", "feedbacks.php",
        "about-settings.php", "welfare-claims.php",
    ]
    for page in pages_cleaned:
        path = ROOT / "admin" / page
        if not path.is_file():
            continue
        content = path.read_text(encoding="utf-8")
        # Check that the specific dead flash block pattern is gone
        assert 'if ($flash = getFlash()):' not in content or \
               'require_once' not in content, (
            f"admin/{page} still has a dead getFlash() call. "
            "admin-header.php already handles flash messages — remove the duplicate."
        )
