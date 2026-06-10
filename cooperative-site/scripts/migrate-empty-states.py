#!/usr/bin/env python3
"""
Admin Empty State Migrator
Replaces inline empty-state <tr> HTML with adminEmptyRow() calls.
Usage: python3 scripts/migrate-empty-states.py [--dry-run]
"""
import re
import sys
import shutil
from pathlib import Path

DRY_RUN = '--dry-run' in sys.argv

# Files to process
ADMIN_DIR = Path(__file__).parent.parent / 'admin'

# Pattern 1: <tr class="no-results-row"><td colspan="N"><i class="fas fa-ICON ..."></i>MESSAGE</td></tr>
# Pattern 2: <tr><td colspan="N" class="text-center py-5 text-muted">...<i ...>...</i>MESSAGE</td></tr>

# Only migrate SIMPLE single-line patterns to avoid breaking complex ones
PATTERNS = [
    # Pattern: <tr class="no-results-row"><td colspan="N"><i class="fas fa-inbox fa-2x d-block mb-2"></i>MESSAGE</td></tr>
    (
        re.compile(
            r'<tr class="no-results-row"><td colspan="(\d+)"><i class="fas (fa-[\w-]+)[^"]*"></i>([^<]*)</td></tr>',
            re.IGNORECASE
        ),
        lambda m: f'<?php echo adminEmptyRow({m.group(1)}, {repr(m.group(3).strip())}); ?>'
    ),
]

total_files  = 0
total_replaced = 0

for php_file in sorted(ADMIN_DIR.glob('*.php')):
    content = php_file.read_text(encoding='utf-8')
    original = content
    file_replacements = 0

    for pattern, replacer in PATTERNS:
        def make_replacement(m):
            return replacer(m)
        new_content, n = pattern.subn(make_replacement, content)
        if n:
            content = new_content
            file_replacements += n

    if file_replacements:
        total_files += 1
        total_replaced += file_replacements
        print(f"  {php_file.name}: {file_replacements} replacement(s)")
        if not DRY_RUN:
            shutil.copy2(php_file, str(php_file) + '.bak-empty')
            php_file.write_text(content, encoding='utf-8')

print(f"\n{'[DRY RUN] ' if DRY_RUN else ''}Total: {total_replaced} replacements in {total_files} files")
