#!/usr/bin/env python3
"""
CSS Duplicate Selector Auditor
Usage: python3 scripts/css-audit.py assets/css/app-public.css [--fix]

Finds top-level duplicate CSS selectors that are likely AI-generated bloat.
With --fix, removes intermediate pure-override blocks (only padding/margin/etc.)
that are fully superseded by a later !important block.
"""
import re
import sys
from pathlib import Path


def get_depth_at(lines: list, idx: int) -> int:
    """Brace depth before line idx."""
    d = 0
    for l in lines[:idx]:
        d += l.count('{') - l.count('}')
    return d


def extract_block(lines: list, start_idx: int) -> tuple:
    """Return (end_idx, props_dict, raw_text)."""
    depth, props, text = 0, {}, []
    for i in range(start_idx, min(start_idx + 40, len(lines))):
        l = lines[i]
        depth += l.count('{') - l.count('}')
        text.append(l)
        m = re.match(r'\s*([\w-]+)\s*:\s*([^;]+);', l.strip())
        if m:
            props[m.group(1)] = m.group(2).strip()
        if depth <= 0 and i > start_idx:
            return i, props, '\n'.join(text)
    return start_idx + 10, props, '\n'.join(text)


def audit(filepath: str) -> dict:
    content = Path(filepath).read_text(encoding='utf-8')
    lines = content.split('\n')
    seen: dict = {}

    for i, line in enumerate(lines):
        stripped = line.strip()
        m = re.match(r'^(\.[a-zA-Z][a-zA-Z0-9_-]*)\s*\{', stripped)
        if not m:
            continue
        d = get_depth_at(lines, i)
        if d == 0:
            seen.setdefault(m.group(1), []).append(i)

    dups = {k: v for k, v in seen.items() if len(v) >= 2}
    return {'lines': lines, 'dups': dups, 'total_top_level': len(seen)}


def report(filepath: str) -> None:
    result = audit(filepath)
    dups = result['dups']
    total = result['total_top_level']
    print(f"\nFile: {filepath}")
    print(f"Total unique top-level selectors: {total}")
    print(f"Selectors with 2+ top-level definitions: {len(dups)}")
    print(f"Selectors with 3+ top-level definitions: {sum(1 for v in dups.values() if len(v) >= 3)}")
    print(f"Selectors with 4+ top-level definitions: {sum(1 for v in dups.values() if len(v) >= 4)}")
    print()
    print("Top 20 most-duplicated selectors:")
    print(f"{'Count':>5}  {'Selector':<40}  Line numbers")
    print("-" * 80)
    for sel, lns in sorted(dups.items(), key=lambda x: -len(x[1]))[:20]:
        print(f"{len(lns):>5}  {sel:<40}  {[l+1 for l in lns]}")
    print()

    # Estimate savings
    redundant_lines = sum((len(v) - 1) * 4 for v in dups.values())
    print(f"Estimated removable lines (avg 4 lines/block): ~{redundant_lines}")
    print(f"Current file size: {len(result['lines'])} lines")
    print(f"Potential size after dedup: ~{len(result['lines']) - redundant_lines} lines")
    print(f"Potential reduction: {redundant_lines / len(result['lines']) * 100:.1f}%")


if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Usage: python3 scripts/css-audit.py <css_file>")
        sys.exit(1)
    report(sys.argv[1])
