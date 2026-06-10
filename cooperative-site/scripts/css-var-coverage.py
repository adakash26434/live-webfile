#!/usr/bin/env python3
"""
CSS Variable Coverage v2 — Replace hardcoded hex colors in CSS + PHP files.

Scan targets:
  1. CSS files (assets/css/)              → replace anywhere in property values
  2. PHP files (admin/, member/, includes/) → replace ONLY in:
       - inline style="..." attributes
       - embedded <style>...</style> blocks
     (PHP data arrays, chart colors, etc. are intentionally skipped)

Only replaces unambiguous 1-to-1 semantic color mappings.

Usage: python3 scripts/css-var-coverage.py [--dry-run]
"""
import re
import sys
from pathlib import Path

DRY_RUN = '--dry-run' in sys.argv
ROOT     = Path(__file__).parent.parent

# ─── Color → CSS variable map ─────────────────────────────────────────────────
COLOR_MAP = {
    '#6b7280':  'var(--text-muted)',
    '#6B7280':  'var(--text-muted)',
    '#9ca3af':  'var(--text-light)',
    '#9CA3AF':  'var(--text-light)',
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
    # Additional semantic colors
    '#f59e0b':  'var(--color-warning)',
    '#fee2e2':  'var(--color-danger-bg)',
    '#d1fae5':  'var(--color-success-bg)',
    '#e5e7eb':  'var(--border-color)',
    '#374151':  'var(--text-dark)',
    '#1f2937':  'var(--text-dark)',
    '#111827':  'var(--text-dark)',
    '#f3f4f6':  'var(--bg-light)',
    '#f9fafb':  'var(--bg-light)',
}

COLOR_KEYS = sorted(COLOR_MAP.keys(), key=len, reverse=True)
_pat_parts = [re.escape(c) for c in COLOR_KEYS]

# For CSS files: replace in property value contexts (after : ; space)
CSS_COLOR_RE = re.compile(
    r'(?<=[:;\s])(' + '|'.join(_pat_parts) + r')\b',
    re.IGNORECASE
)

# For PHP inline styles: replace within style="..." attribute values
INLINE_STYLE_RE = re.compile(
    r'(style=["\'])([^"\']*?)(["\'])',
    re.IGNORECASE | re.DOTALL
)

# For PHP embedded <style> blocks
STYLE_BLOCK_RE = re.compile(
    r'(<style[^>]*>)([\s\S]*?)(</style>)',
    re.IGNORECASE
)

def _replace_in_css(text: str) -> str:
    """Replace hex colors in a CSS-context string."""
    def _repl(m: re.Match) -> str:
        original = m.group(1)
        return COLOR_MAP.get(original, COLOR_MAP.get(original.lower(), original))
    return CSS_COLOR_RE.sub(_repl, text)

def _replace_in_php_inline(text: str) -> tuple[str, int]:
    """Replace hex colors only within style="..." attributes."""
    count = [0]

    def _style_repl(m: re.Match) -> str:
        quote_open, css_val, quote_close = m.group(1), m.group(2), m.group(3)
        new_val = _replace_in_css(css_val)
        if new_val != css_val:
            count[0] += css_val.count('#')
        return quote_open + new_val + quote_close

    new_text = INLINE_STYLE_RE.sub(_style_repl, text)

    def _block_repl(m: re.Match) -> str:
        open_tag, css_body, close_tag = m.group(1), m.group(2), m.group(3)
        new_body = _replace_in_css(css_body)
        if new_body != css_body:
            count[0] += css_body.count('#')
        return open_tag + new_body + close_tag

    new_text = STYLE_BLOCK_RE.sub(_block_repl, new_text)
    return new_text, count[0]

# ─── CSS file scan ────────────────────────────────────────────────────────────
CSS_GLOBS = [
    'assets/css/*.css',
    'assets/css/*.php',
    'includes/*.css',
]

# ─── PHP template scan (style= and <style> only) ─────────────────────────────
PHP_GLOBS = [
    'admin/**/*.php',
    'member/**/*.php',
    'includes/**/*.php',
    '*.php',
]

total_files    = 0
total_replaced = 0

print("── CSS files ────────────────────────────────────────────")
for glob in CSS_GLOBS:
    for f in sorted(ROOT.glob(glob)):
        try:
            content = f.read_text(encoding='utf-8')
        except Exception:
            continue
        if f.name == 'global-theme.php':
            continue
        new_content = _replace_in_css(content)
        n = content.count(new_content) == 0 and new_content != content
        if n:
            total_files += 1
            total_replaced += sum(1 for a, b in zip(content, new_content) if a != b)
            if not DRY_RUN:
                f.write_text(new_content, encoding='utf-8')
            rel = str(f.relative_to(ROOT))
            print(f"  {rel}")

print("── PHP templates (style= / <style> only) ────────────────")
for glob in PHP_GLOBS:
    for f in sorted(ROOT.glob(glob)):
        if 'archive_old_v1' in str(f) or '.git' in str(f):
            continue
        try:
            content = f.read_text(encoding='utf-8')
        except Exception:
            continue
        new_content, n = _replace_in_php_inline(content)
        if n and new_content != content:
            total_files += 1
            total_replaced += n
            if not DRY_RUN:
                f.write_text(new_content, encoding='utf-8')
            rel = str(f.relative_to(ROOT))
            print(f"  {rel} (~{n} replacements)")

print(f"\n{'[DRY RUN] ' if DRY_RUN else ''}Total: ~{total_replaced} replacements in {total_files} files")
