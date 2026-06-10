#!/usr/bin/env python3
"""
Safe CSS Deduplicator
Removes intermediate top-level blocks whose ALL properties are overridden
by a later block of the same selector.

Usage: python3 scripts/css-dedup.py <css_file> [--dry-run]
"""
import re
import sys
import shutil
from pathlib import Path


def get_depth_at(lines: list, idx: int) -> int:
    d = 0
    for l in lines[:idx]:
        d += l.count('{') - l.count('}')
    return d


def extract_block(lines: list, start: int) -> tuple[int, dict, str]:
    """Return (end_idx, props_dict, full_text).  props are {name: (value, is_important)}."""
    depth, props, text = 0, {}, []
    for i in range(start, min(start + 60, len(lines))):
        l = lines[i]
        depth += l.count('{') - l.count('}')
        text.append(l)
        m = re.match(r'\s*([\w-]+)\s*:\s*(.+?)\s*(!important)?\s*;', l)
        if m:
            key = m.group(1)
            val = m.group(2).strip().rstrip('!important').strip()
            imp = bool(m.group(3))
            props[key] = (val, imp)
        if depth <= 0 and i > start:
            return i, props, '\n'.join(text)
    return start + 10, props, '\n'.join(text[:10])


def is_subset_of_later(early_props: dict, later_blocks: list[dict]) -> bool:
    """True if every property in early_props is overridden by at least one later block."""
    for prop in early_props:
        # Check if any LATER block overrides this property
        overridden = any(prop in lb for lb in later_blocks)
        if not overridden:
            return False
    return True


def deduplicate(filepath: str, dry_run: bool = False) -> dict:
    content = Path(filepath).read_text(encoding='utf-8')
    lines = content.split('\n')

    # Find all top-level blocks per selector
    seen: dict = {}  # selector -> [(start_idx, end_idx, props, text)]
    i = 0
    while i < len(lines):
        stripped = lines[i].strip()
        m = re.match(r'^(\.[a-zA-Z][a-zA-Z0-9_-]*)\s*\{', stripped)
        if m and get_depth_at(lines, i) == 0:
            sel = m.group(1)
            end, props, text = extract_block(lines, i)
            seen.setdefault(sel, []).append((i, end, props, text))
        i += 1

    # Find safe removals: for each duplicate selector,
    # check intermediate blocks (not the last) that are subsets of all blocks after them
    lines_to_remove: set = set()
    stats = {'selectors': 0, 'blocks_removed': 0, 'lines_removed': 0}

    for sel, blocks in seen.items():
        if len(blocks) < 2:
            continue

        removed_any = False
        for idx in range(len(blocks) - 1):  # skip last block
            start, end, props, text = blocks[idx]
            later_props = [b[2] for b in blocks[idx + 1:]]

            # Only remove if block has properties AND they're all overridden later
            if props and is_subset_of_later(props, later_props):
                for line_idx in range(start, end + 1):
                    lines_to_remove.add(line_idx)
                    # Also remove the blank line before the block if any
                    if start > 0 and lines[start - 1].strip() == '':
                        lines_to_remove.add(start - 1)
                stats['blocks_removed'] += 1
                stats['lines_removed'] += (end - start + 1)
                removed_any = True

        if removed_any:
            stats['selectors'] += 1

    if not lines_to_remove:
        print(f"No safe removals found in {filepath}")
        return stats

    # Build new content excluding removed lines
    new_lines = [l for i, l in enumerate(lines) if i not in lines_to_remove]
    new_content = '\n'.join(new_lines)

    print(f"\n{'[DRY RUN] ' if dry_run else ''}Results for {filepath}:")
    print(f"  Selectors with safe removals: {stats['selectors']}")
    print(f"  Blocks removed: {stats['blocks_removed']}")
    print(f"  Lines removed: {stats['lines_removed']}")
    print(f"  Original: {len(lines)} lines → New: {len(new_lines)} lines")

    if not dry_run:
        # Backup original
        bak = filepath + '.bak-dedup'
        shutil.copy2(filepath, bak)
        Path(filepath).write_text(new_content, encoding='utf-8')
        print(f"  Saved. Backup: {bak}")
    else:
        print("  (dry run — no changes written)")

    return stats


if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Usage: python3 scripts/css-dedup.py <css_file> [--dry-run]")
        sys.exit(1)
    dry = '--dry-run' in sys.argv
    deduplicate(sys.argv[1], dry_run=dry)
