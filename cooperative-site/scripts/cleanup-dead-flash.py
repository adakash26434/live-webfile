#!/usr/bin/env python3
"""
Dead Flash Message Cleanup v2
Admin-header.php already handles getFlash() in the admin layout.
Individual admin pages that call getFlash() AFTER including admin-header are dead code.

Usage: python3 scripts/cleanup-dead-flash.py [--dry-run]
"""
import re
import sys
from pathlib import Path

DRY_RUN = '--dry-run' in sys.argv
ADMIN_DIR = Path(__file__).parent.parent / 'admin'

# The dead pattern — same structure in all affected files.
# Uses DOTALL so .* matches across multiple lines.
DEAD_FLASH = re.compile(
    r'[ \t]*<?php\s*\n?'
    r'[ \t]*if\s*\(\s*\$flash\s*=\s*getFlash\(\)\s*\)\s*[:\?]>?\s*\n'
    r'[ \t]*<div class="alert alert-<\?php echo \$flash\[.type.\]'
    r'[^\n]*\n'
    r'[ \t]*<\?php endif; \?>\s*\n?',
    re.DOTALL
)

# Alternative format (4-liner with ?> on separate line)
DEAD_FLASH_ALT = re.compile(
    r'[ \t]*if\s*\(\s*\$flash\s*=\s*getFlash\(\)\s*\)\s*:\s*\n'
    r'[ \t]*\?>\s*\n'
    r'[ \t]*<div class="alert alert-[^\n]+\n'
    r'[ \t]*<\?php endif; \?>\s*\n',
    re.DOTALL
)

total_files    = 0
total_replaced = 0

SKIP = {'admin-header.php', 'admin-footer.php', 'admin-ui.php', '_bootstrap.php'}

for php_file in sorted(ADMIN_DIR.glob('*.php')):
    if php_file.name in SKIP:
        continue
    content = php_file.read_text(encoding='utf-8')

    # Only process files that include admin-header
    if "require_once 'includes/admin-header.php'" not in content and \
       'require_once "includes/admin-header.php"' not in content:
        continue

    original = content
    n = 0

    new_content, c1 = DEAD_FLASH.subn('', content)
    n += c1
    content = new_content

    new_content, c2 = DEAD_FLASH_ALT.subn('', content)
    n += c2
    content = new_content

    if n:
        total_files += 1
        total_replaced += n
        print(f"  {php_file.name}: {n} dead flash block(s) removed")
        if not DRY_RUN:
            php_file.write_text(content, encoding='utf-8')

print(f"\n{'[DRY RUN] ' if DRY_RUN else ''}Total: {total_replaced} removals in {total_files} files")
