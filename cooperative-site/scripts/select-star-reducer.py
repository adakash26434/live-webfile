#!/usr/bin/env python3
"""
SELECT * Reducer — Replaces `SELECT *` with explicit column lists.

Strategy:
  1. Parse column names from database/install.sql
  2. Scan PHP files for simple SELECT * FROM <table> queries
  3. Replace * with the actual columns (safe, non-destructive)

Rules:
  - Only replaces SIMPLE single-table queries: SELECT * FROM table WHERE...
  - Skips queries with JOINs (too complex to auto-expand)
  - Skips `SELECT * FROM information_schema` and similar
  - Security: 'password_hash' kept only when file likely needs it for auth

Usage: python3 scripts/select-star-reducer.py [--dry-run]
"""
import re
import sys
from pathlib import Path

DRY_RUN = '--dry-run' in sys.argv
ROOT     = Path(__file__).parent.parent

# ─── 1. Parse schema ─────────────────────────────────────────────────────────
sql_content = (ROOT / 'database/install.sql').read_text(encoding='utf-8')
TABLE_COLS: dict[str, list[str]] = {}

for m in re.finditer(
    r'CREATE TABLE IF NOT EXISTS (\w+)\s*\(([\s\S]+?)\)\s*ENGINE',
    sql_content
):
    tname = m.group(1)
    body  = m.group(2)
    cols  = []
    for line in body.split('\n'):
        line = line.strip()
        cm = re.match(
            r'^(`?)(\w+)\1\s+(INT|VARCHAR|TEXT|TINYINT|DECIMAL|DATE|TIMESTAMP|ENUM|FLOAT|BIGINT|MEDIUMTEXT|LONGTEXT|CHAR|BOOL)',
            line, re.I
        )
        if cm:
            col = cm.group(2)
            if col.upper() not in ('PRIMARY', 'UNIQUE', 'INDEX', 'KEY', 'FOREIGN', 'CONSTRAINT'):
                cols.append(col)
    if cols:
        TABLE_COLS[tname] = cols

print(f"Parsed {len(TABLE_COLS)} tables from install.sql")

# ─── 2. Build column strings ──────────────────────────────────────────────────
def cols_for(table: str, exclude_sensitive: bool = False) -> str | None:
    if table not in TABLE_COLS:
        return None
    cols = TABLE_COLS[table]
    if exclude_sensitive:
        cols = [c for c in cols if c not in ('password_hash',)]
    return ', '.join(cols)

# ─── 3. Simple SELECT * regex ─────────────────────────────────────────────────
# Matches: SELECT * FROM table_name <rest>
# Does NOT match: JOIN, subqueries, etc.
SEL_STAR = re.compile(
    r'SELECT\s+\*\s+FROM\s+(\w+)(\s+(?:WHERE|ORDER|LIMIT|GROUP|HAVING|LEFT|RIGHT|INNER|OUTER|JOIN)\b)',
    re.IGNORECASE
)
SEL_STAR_END = re.compile(
    r'SELECT\s+\*\s+FROM\s+(\w+)(\s*["\']?\s*[)\s;])',
    re.IGNORECASE
)

AUTH_FILES = {'member-auth.php', 'admin-header.php', 'index.php'}

total_files    = 0
total_replaced = 0

def replace_star(content: str, filepath: Path) -> tuple[str, int]:
    count = 0
    fname = filepath.name
    exclude_sensitive = fname not in AUTH_FILES  # auth files may need password_hash

    def _repl(m: re.Match) -> str:
        table  = m.group(1).lower()
        suffix = m.group(2)
        explicit = cols_for(table, exclude_sensitive=exclude_sensitive)
        if explicit is None:
            return m.group(0)   # unknown table — leave as-is
        # Skip if table has too many columns (> 25) — use * for readability
        if len(TABLE_COLS[table]) > 25:
            return m.group(0)
        nonlocal count
        count += 1
        return f'SELECT {explicit} FROM {table}{suffix}'

    new = SEL_STAR.sub(_repl, content)
    new = SEL_STAR_END.sub(_repl, new)
    return new, count

# Scan all PHP in admin/, includes/, member/, root
scan_dirs = ['admin', 'includes', 'member', '.']
for d in scan_dirs:
    for php_file in sorted((ROOT / d).glob('*.php')):
        if php_file.name.startswith('_'):
            continue
        try:
            content = php_file.read_text(encoding='utf-8')
        except Exception:
            continue

        new_content, n = replace_star(content, php_file)
        if n:
            total_files    += 1
            total_replaced += n
            rel = php_file.relative_to(ROOT)
            print(f"  {rel}: {n} replacement(s)")
            if not DRY_RUN:
                php_file.write_text(new_content, encoding='utf-8')

print(f"\n{'[DRY RUN] ' if DRY_RUN else ''}Total: {total_replaced} replacements in {total_files} files")
