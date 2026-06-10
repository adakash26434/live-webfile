#!/usr/bin/env python3
"""
Confirm Dialog Migration — Replaces native browser confirm() with data-confirm attribute.

Converts:
  onsubmit="return confirm('message')"
  onsubmit="return confirm(\"message\")"
  onsubmit="return confirm('<?php echo $phpVar; ?>')"
to:
  data-confirm="message"  (or data-confirm="<?php echo ...?>")

Also handles:
  onclick="return confirm('message')"  on <button type="submit"> and <a> elements

Usage: python3 scripts/confirm-migration.py [--dry-run]

NOTE: confirm-modal.js must be loaded globally (already done in admin-footer.php)
"""
from __future__ import annotations
import re
import sys
from pathlib import Path

DRY_RUN = '--dry-run' in sys.argv
ROOT     = Path(__file__).parent.parent

# ─── Pattern 1: <form ... onsubmit="return confirm('...')"> ──────────────────
# Matches both single and double quoted confirm messages (including PHP inside)

FORM_ONSUBMIT_RE = re.compile(
    r'''onsubmit\s*=\s*(?:"return confirm\((['"'])(.*?)\1\)"|'return confirm\((['"'])(.*?)\3\)')''',
    re.DOTALL | re.IGNORECASE
)

# More permissive pattern for complex cases (with PHP expressions inside)
# Handles: semicolon before closing quote, escaped quotes inside
FORM_ONSUBMIT_COMPLEX_RE = re.compile(
    r"""onsubmit\s*=\s*["']return confirm\(([\s\S]*?)\)\s*;?\s*["']""",
    re.IGNORECASE
)

# PHP translation function pattern: onsubmit="return confirm('<?php echo $fn(...); ?>');"
PHP_TRANS_CONFIRM_RE = re.compile(
    r"""onsubmit\s*=\s*"return confirm\('(<\?php[\s\S]*?\?>)'\)\s*;?"\s*""",
    re.IGNORECASE
)

# ─── Pattern 2: onclick="return confirm('...')" on buttons/links ─────────────
BTN_ONCLICK_RE = re.compile(
    r"""onclick\s*=\s*["']return confirm\(([\s\S]*?)\)["']""",
    re.IGNORECASE
)

def extract_message(raw_arg: str) -> str | None:
    """
    Extract the confirm message string from the raw argument inside confirm(...).
    Handles: 'msg', "msg", \'msg\', \"msg\", '<?php echo $var; ?>'
    Returns None if too complex to handle safely.
    """
    raw = raw_arg.strip()
    # Unescape PHP string-escaped quotes: \' → ' and \" → "
    raw = raw.replace("\\'", "'").replace('\\"', '"')

    # PHP expressions with nested function calls — too complex, skip safely
    if '<?php' in raw or "<?=" in raw:
        # Only handle simple cases: '<?php echo $simpleVar; ?>' or "<?= $x ?>"
        # Check if it's a simple wrapping: outer quotes + PHP + closing ?>quote
        if raw.startswith("'") and raw.endswith("?>'"):
            return raw[1:-1]   # '<?php ... ?>' → <?php ... ?>
        if raw.startswith('"') and raw.endswith('?>"'):
            return raw[1:-1]   # "<?php ... ?>" → <?php ... ?>
        # Complex PHP (nested calls like $_t(...)) → skip
        return None

    # Simple single-quoted string
    if raw.startswith("'") and raw.endswith("'"):
        return raw[1:-1]
    # Simple double-quoted string
    if raw.startswith('"') and raw.endswith('"'):
        return raw[1:-1]
    return None

def migrate_file(path: Path) -> tuple[int, list[str]]:
    content = path.read_text(encoding='utf-8')
    original = content
    changed_lines = []
    count = 0

    # ── PHP translation function confirm (must run BEFORE the general pattern) ──
    def replace_php_confirm(m: re.Match) -> str:
        php_expr = m.group(1)
        nonlocal count
        count += 1
        return f'data-confirm="{php_expr}" '

    content = PHP_TRANS_CONFIRM_RE.sub(replace_php_confirm, content)

    # ── Form onsubmit replacement ──────────────────────────────────────────
    def replace_form_onsubmit(m: re.Match) -> str:
        full_match = m.group(0)
        # Extract the raw inner argument
        inner = full_match
        # Find the argument between confirm( and )
        inner_m = re.search(r"confirm\(([\s\S]*?)\)\s*;?\s*[\"']", full_match, re.IGNORECASE)
        if not inner_m:
            return full_match  # can't parse, leave as-is
        raw_arg = inner_m.group(1)
        msg = extract_message(raw_arg)
        if msg is None:
            return full_match  # too complex, leave as-is

        nonlocal count
        count += 1
        return f'data-confirm="{msg}"'

    content = FORM_ONSUBMIT_COMPLEX_RE.sub(replace_form_onsubmit, content)

    # ── Button/link onclick replacement ───────────────────────────────────
    def replace_btn_onclick(m: re.Match) -> str:
        full_match = m.group(0)
        inner_m = re.search(r"confirm\(([\s\S]*?)\)\s*;?\s*[\"']", full_match, re.IGNORECASE)
        if not inner_m:
            return full_match
        raw_arg = inner_m.group(1)
        msg = extract_message(raw_arg)
        if msg is None:
            return full_match

        nonlocal count
        count += 1
        # For buttons: replace onclick with data-confirm-href only if it's an <a> tag
        # For form submit buttons: use data-confirm on the form (best handled at form level)
        # Since we can't tell from onclick alone, keep as onclick but use coopConfirm
        # This makes it work with our JS confirm modal
        return f'onclick="event.preventDefault();window.coopConfirm({raw_arg},function(){{this.closest(\'form\')||this.click();}}.bind(this));return false;"'

    content = BTN_ONCLICK_RE.sub(replace_btn_onclick, content)

    if content != original:
        if not DRY_RUN:
            path.write_text(content, encoding='utf-8')
        # Find changed lines
        for i, (a, b) in enumerate(zip(original.split('\n'), content.split('\n')), 1):
            if a != b:
                changed_lines.append(f"  Line {i}: {b.strip()[:80]}")

    return count, changed_lines

# ─── Scan all PHP files ───────────────────────────────────────────────────────
targets = (
    list(ROOT.glob('admin/**/*.php')) +
    list(ROOT.glob('member/**/*.php'))
)

total_files = 0
total_changes = 0

for path in sorted(targets):
    if 'archive_old_v1' in str(path):
        continue
    n, lines = migrate_file(path)
    if n:
        total_files += 1
        total_changes += n
        rel = str(path.relative_to(ROOT))
        print(f"\n{'='*60}")
        print(f"FILE: {rel} ({n} conversions)")
        for l in lines[:10]:
            print(l)
        if len(lines) > 10:
            print(f"  ... and {len(lines)-10} more")

print(f"\n{'='*60}")
print(f"{'[DRY RUN] ' if DRY_RUN else ''}TOTAL: {total_changes} confirm() → data-confirm conversions in {total_files} files")
