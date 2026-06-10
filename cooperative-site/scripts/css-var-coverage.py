#!/usr/bin/env python3
"""
CSS Variable Coverage — Replace hardcoded hex colors in PHP/CSS files
with the CSS custom properties already defined in global-theme.php.

Maps the most common hardcoded values → CSS variable equivalents.
Only replaces SAFE, unambiguous 1-to-1 mappings (no heuristics).

Usage: python3 scripts/css-var-coverage.py [--dry-run]
"""
import re
import sys
from pathlib import Path

DRY_RUN = '--dry-run' in sys.argv
ROOT     = Path(__file__).parent.parent

# ─── Color → CSS variable map ─────────────────────────────────────────────────
# These are the colors ALREADY defined in global-theme.php as CSS variables.
# We only map colors that have an EXACT 1-to-1 CSS variable.
COLOR_MAP = {
    # Text colors — safe because these are ALWAYS muted/secondary text
    '#6b7280':  'var(--text-muted)',
    '#6B7280':  'var(--text-muted)',
    '#9ca3af':  'var(--text-light)',
    '#9CA3AF':  'var(--text-light)',
    # Status colors — safe, always represent the same semantic
    '#16a34a':  'var(--color-success)',
    '#d97706':  'var(--color-warning)',
    '#dc2626':  'var(--color-danger)',
    '#0891b2':  'var(--color-info)',
    '#f0fdf4':  'var(--color-success-bg)',
    '#fffbeb':  'var(--color-warning-bg)',
    '#fef2f2':  'var(--color-danger-bg)',
    '#ecfeff':  'var(--color-info-bg)',
    '#bbf7d0':  'var(--color-success-border)',
    '#fde68a':  'var(--color-warning-border)',
    '#fecaca':  'var(--color-danger-border)',
    '#a5f3fc':  'var(--color-info-border)',
    # NOTE: #fff, #ffffff, #f8faf9, etc. are intentionally excluded —
    # they appear as BOTH background AND text-on-dark contexts.
    # Replacing them universally would break dark mode.
}

# Build regex that matches any of these colors in CSS property value context
COLOR_KEYS  = sorted(COLOR_MAP.keys(), key=len, reverse=True)  # longest first
_pat_parts  = [re.escape(c) for c in COLOR_KEYS]
COLOR_RE    = re.compile(
    r'(?<=[:;\s])(' + '|'.join(_pat_parts) + r')\b',
    re.IGNORECASE
)

# Only apply to CSS/PHP files — not SQL, JS, or lock files
SCAN_GLOBS = [
    'assets/css/*.css',
    'assets/css/*.php',
    'member/includes/*.php',
    'includes/*.css',
]

total_files    = 0
total_replaced = 0

for glob in SCAN_GLOBS:
    for f in sorted(ROOT.glob(glob)):
        try:
            content = f.read_text(encoding='utf-8')
        except Exception:
            continue

        # Skip global-theme.php itself (it defines the vars)
        if f.name == 'global-theme.php':
            continue

        def _repl(m: re.Match) -> str:
            original = m.group(1)
            return COLOR_MAP.get(original, COLOR_MAP.get(original.lower(), original))

        new_content = COLOR_RE.sub(_repl, content)
        n = content != new_content

        if n:
            # Count actual replacements
            cnt = sum(content.count(k) - new_content.count(k) for k in COLOR_MAP if COLOR_MAP[k] != k)
            cnt = max(cnt, 1)
            total_files    += 1
            total_replaced += cnt
            rel = f.relative_to(ROOT)
            print(f"  {rel}: ~{cnt} color(s) replaced")
            if not DRY_RUN:
                f.write_text(new_content, encoding='utf-8')

print(f"\n{'[DRY RUN] ' if DRY_RUN else ''}Total: ~{total_replaced} replacements in {total_files} files")
