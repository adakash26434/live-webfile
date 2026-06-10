#!/usr/bin/env python3
"""
Enhanced Admin Empty State Migrator — handles multi-line patterns.
Replaces inline empty-state <tr> HTML with adminEmptyRow() calls.
Usage: python3 scripts/migrate-empty-states-v2.py [--dry-run]
"""
import re
import sys
import shutil
from pathlib import Path

DRY_RUN = '--dry-run' in sys.argv
ADMIN_DIR = Path(__file__).parent.parent / 'admin'

# Matches multi-line pattern:
# <tr><td colspan="N" class="text-center py-5 text-muted">
#     <i class="fas fa-ICON ..."></i>
#     MESSAGE
# </td></tr>
MULTI_LINE = re.compile(
    r'<tr>\s*<td colspan="(\d+)"\s+class="text-center\s+py-[45][^"]*">\s*'
    r'(?:<div[^>]*>\s*)?'
    r'<i class="fas (fa-[\w-]+)[^"]*"></i>\s*'
    r'(?:<h[1-6][^>]*>)?([^<]{2,120})(?:</h[1-6]>)?\s*'
    r'(?:<small>([^<]{0,80})</small>\s*)?'
    r'(?:</div>\s*)?'
    r'</td>\s*</tr>',
    re.DOTALL | re.IGNORECASE
)

def make_replacement(m):
    colspan = m.group(1)
    icon    = m.group(2).replace('fa-', '', 1)  # strip 'fa-' prefix
    msg     = m.group(3).strip().strip('\n').strip()
    sub     = (m.group(4) or '').strip()
    if sub:
        return f"<?php echo adminEmptyRow({colspan}, {repr(msg)}, {repr(sub)}, {repr(icon)}); ?>"
    return f"<?php echo adminEmptyRow({colspan}, {repr(msg)}, '', {repr(icon)}); ?>"

total_files     = 0
total_replaced  = 0

for php_file in sorted(ADMIN_DIR.glob('*.php')):
    if php_file.name in ('admin-ui.php', 'admin-header.php', 'admin-footer.php', '_bootstrap.php'):
        continue
    content = php_file.read_text(encoding='utf-8')
    new_content, n = MULTI_LINE.subn(make_replacement, content)
    if n:
        total_files += 1
        total_replaced += n
        print(f"  {php_file.name}: {n} replacement(s)")
        if not DRY_RUN:
            php_file.write_text(new_content, encoding='utf-8')

print(f"\n{'[DRY RUN] ' if DRY_RUN else ''}Total: {total_replaced} replacements in {total_files} files")
