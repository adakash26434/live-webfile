#!/usr/bin/env python3
"""
CSS Smart Deduplicator v2 — Merges duplicate top-level selector blocks.

Algorithm:
  1. Parse the CSS file into a sequence of "blocks" (selector + properties)
     and "raw" chunks (comments, @rules, media queries).
  2. For each top-level selector that appears more than once, collect ALL
     property declarations across all blocks.
  3. Merge properties: last occurrence wins for same-property conflicts;
     !important always wins over non-!important.
  4. Keep only the LAST occurrence of each duplicate selector (with merged
     props), remove all earlier occurrences.

Safety guarantees:
  - Only merges simple top-level selectors (no @media, no nested selectors).
  - Preserves @media/@keyframes/@font-face blocks unchanged.
  - Preserves comments in place.
  - Skips :root (it has dynamic PHP output).

Usage: python3 scripts/css-dedup-v2.py <file.css> [--dry-run]
"""
import re
import sys
from pathlib import Path
from collections import defaultdict
from copy import deepcopy

if len(sys.argv) < 2:
    print(f"Usage: python3 {sys.argv[0]} <file.css> [--dry-run]")
    sys.exit(1)

CSS_FILE = Path(sys.argv[1])
DRY_RUN  = '--dry-run' in sys.argv

if not CSS_FILE.exists():
    print(f"File not found: {CSS_FILE}")
    sys.exit(1)

# ─── Tokenize CSS into segments ───────────────────────────────────────────────
content = CSS_FILE.read_text(encoding='utf-8')

# We split the file into chunks:
#   - AT_RULE:   @media, @keyframes, @font-face, etc. (keep as-is)
#   - COMMENT:   /* ... */ (keep as-is)
#   - BLOCK:     selector { ... }  (may be deduplicated)
#   - RAW:       anything else (whitespace, line breaks between blocks)

CHUNK_RE = re.compile(
    r'(/\*[\s\S]*?\*/)'          # group 1: comment
    r'|(@[\w-]+(?:[^{;]+)?'      # group 2+3: at-rule with block
    r'(?:\{(?:[^{}]|\{[^{}]*\})*\}|;))'
    r'|([^{}@/]+\{[^{}]*\})'     # group 4: simple selector { props }
    r'|([^{}]+)',                 # group 5: raw/whitespace
    re.DOTALL
)

# ─── Helpers ─────────────────────────────────────────────────────────────────

def normalize_selector(sel: str) -> str:
    return re.sub(r'\s+', ' ', sel.strip())

def parse_props(block_content: str) -> list[tuple[str, str]]:
    """Return list of (property, value) from block content."""
    props = []
    for m in re.finditer(r'([\w-]+)\s*:\s*([^;}{]+?)\s*;', block_content):
        props.append((m.group(1).strip(), m.group(2).strip()))
    return props

def merge_props(prop_list: list[tuple[str, str]]) -> dict[str, str]:
    """Merge properties: !important beats non-!important, otherwise last wins."""
    merged: dict[str, str] = {}
    for prop, val in prop_list:
        if prop not in merged:
            merged[prop] = val
        else:
            existing = merged[prop]
            # !important always wins
            if '!important' in val:
                merged[prop] = val
            elif '!important' not in existing:
                merged[prop] = val  # last wins
    return merged

def props_to_str(merged: dict[str, str], indent: str = '    ') -> str:
    lines = [f'{indent}{prop}: {val};' for prop, val in merged.items()]
    return '\n'.join(lines)

# ─── Parse file into segments ─────────────────────────────────────────────────
segments = []  # list of (type, selector_or_none, raw_text, props_list)

for m in CHUNK_RE.finditer(content):
    if m.group(1):
        # Comment
        segments.append(('comment', None, m.group(1), []))
    elif m.group(2):
        # At-rule (keep as-is)
        segments.append(('at_rule', None, m.group(2), []))
    elif m.group(4):
        # Simple block: "selector { props }"
        raw = m.group(4)
        if '{' not in raw:
            segments.append(('raw', None, raw, []))
            continue
        brace = raw.index('{')
        last_close = raw.rindex('}') if '}' in raw else -1
        if last_close < 0:
            segments.append(('raw', None, raw, []))
            continue
        selector = normalize_selector(raw[:brace])
        block_content = raw[brace+1:last_close]
        props = parse_props(block_content)
        # Skip :root and selectors with nested {}
        if not selector or ':root' in selector or '{' in block_content:
            segments.append(('at_rule', None, raw, []))
        else:
            segments.append(('block', selector, raw, props))
    else:
        raw_text = m.group(0)
        segments.append(('raw', None, raw_text, []))

# ─── Find duplicates ─────────────────────────────────────────────────────────
sel_indices: dict[str, list[int]] = defaultdict(list)
for i, (seg_type, selector, raw, props) in enumerate(segments):
    if seg_type == 'block' and selector:
        sel_indices[selector].append(i)

duplicates = {sel: idxs for sel, idxs in sel_indices.items() if len(idxs) > 1}
print(f"Found {len(duplicates)} duplicate selectors to merge")

# ─── Merge and eliminate ─────────────────────────────────────────────────────
removed_count = 0
merged_count  = 0

for selector, idxs in duplicates.items():
    # Collect all properties across all occurrences
    all_props = []
    for i in idxs:
        all_props.extend(segments[i][3])

    merged = merge_props(all_props)
    merged_text = props_to_str(merged)

    # Replace LAST occurrence with merged content
    last_idx = idxs[-1]
    orig_raw = segments[last_idx][2]
    brace = orig_raw.index('{')
    close = orig_raw.rindex('}')
    # Preserve original selector formatting
    orig_sel = orig_raw[:brace].rstrip()
    new_raw = f"{orig_sel} {{\n{merged_text}\n}}"
    segments[last_idx] = ('block', selector, new_raw, list(merged.items()))

    # Remove all earlier occurrences
    for i in idxs[:-1]:
        segments[i] = ('removed', None, '', [])
        removed_count += 1

    merged_count += 1

# ─── Reconstruct CSS ─────────────────────────────────────────────────────────
output_parts = []
for seg_type, _, raw, _ in segments:
    if seg_type != 'removed':
        output_parts.append(raw)

output = ''.join(output_parts)

# Clean up excessive blank lines (max 2 consecutive newlines)
output = re.sub(r'\n{4,}', '\n\n\n', output)

# ─── Report ──────────────────────────────────────────────────────────────────
orig_lines = content.count('\n')
new_lines  = output.count('\n')
reduction  = orig_lines - new_lines

print(f"  Merged:  {merged_count} selectors")
print(f"  Removed: {removed_count} duplicate blocks")
print(f"  Lines:   {orig_lines} → {new_lines} (saved {reduction} lines, {100*reduction/orig_lines:.1f}%)")

if DRY_RUN:
    print(f"\n[DRY RUN] No file written.")
else:
    # Backup
    backup = CSS_FILE.with_suffix('.css.bak2')
    backup.write_text(content, encoding='utf-8')
    CSS_FILE.write_text(output, encoding='utf-8')
    print(f"\nWritten to {CSS_FILE} (backup: {backup.name})")
