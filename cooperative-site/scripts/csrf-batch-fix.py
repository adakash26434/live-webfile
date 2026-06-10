#!/usr/bin/env python3
"""
CSRF Batch Fixer — Adds checkCSRF() after every top-level POST handler
in admin pages that include admin-header.php.
"""
import re, sys
from pathlib import Path

DRY_RUN = '--dry-run' in sys.argv
ADMIN   = Path(__file__).parent.parent / 'admin'

# Patterns for the POST check line (all variants)
POST_PAT = re.compile(
    r"(if\s*\(\s*\$_SERVER\s*\[\s*'REQUEST_METHOD'\s*\]\s*===\s*'POST'"
    r"[^)]*\)\s*\{)",
    re.MULTILINE
)

total = 0

for php in sorted(ADMIN.glob('*.php')):
    content = php.read_text(encoding='utf-8')

    # Only process files that include admin-header AND don't already have checkCSRF
    if 'admin-header.php' not in content:
        continue
    if 'checkCSRF()' in content or 'verifyCSRFToken()' in content:
        continue
    # Must have a POST handler
    if "REQUEST_METHOD'] === 'POST'" not in content:
        continue

    # Insert checkCSRF() on next line after every POST check opening brace
    def repl(m):
        return m.group(1) + '\n    checkCSRF();'

    new_content = POST_PAT.sub(repl, content)
    if new_content != content:
        total += 1
        print(f"  {php.name}")
        if not DRY_RUN:
            php.write_text(new_content, encoding='utf-8')

print(f"\n{'[DRY RUN] ' if DRY_RUN else ''}CSRF added to {total} files")
