#!/usr/bin/env python3
"""
CSS Smart Deduplicator v3 — Fixed group bug + improved parser.

Algorithm:
  1. Parse CSS file into segments: comments, @rules (kept as-is), rule blocks.
  2. For each top-level selector appearing 2+ times, merge all property
     declarations (last occurrence wins; !important beats non-!important).
  3. Place merged block at last occurrence position, remove earlier occurrences.

Safety:
  - Only merges simple top-level selectors (not inside @media).
  - Preserves @media/@keyframes/@font-face blocks unchanged.
  - Preserves comments in place.
  - Writes backup .bak before modifying.

Usage:
  python3 scripts/css-dedup-v3.py <file.css> [--dry-run]
"""
from __future__ import annotations
import re, sys
from pathlib import Path
from collections import defaultdict

if len(sys.argv) < 2:
    sys.exit(f"Usage: python3 {sys.argv[0]} <file.css> [--dry-run]")

CSS_FILE = Path(sys.argv[1])
DRY_RUN  = '--dry-run' in sys.argv

if not CSS_FILE.exists():
    sys.exit(f"File not found: {CSS_FILE}")

content = CSS_FILE.read_text(encoding='utf-8')

# ─── Tokenizer ────────────────────────────────────────────────────────────────
# We build a list of Segment objects from the raw file content.
# Each segment is one of:
#   'comment'  — /* ... */
#   'at_rule'  — @media { ... } / @keyframes { ... } / @charset ; etc.
#   'block'    — selector { properties }
#   'raw'      — whitespace / blank lines between blocks

SEG_RE = re.compile(
    r'(/\*[\s\S]*?\*/)'                           # G1  comment
    r'|(@[\w-]+[^{;]*;)'                          # G2  @-rule with semicolon (@charset, @import)
    r'|(@[\w-]+[^{]*\{(?:[^{}]|\{[^{}]*\})*\})'  # G3  @-rule with block (@media, @keyframes, etc.)
    r'|([^{}/]+\{[^{}]*\})',                       # G4  simple rule block
    re.DOTALL
)

class Seg:
    __slots__ = ('kind','selector','raw','props_ordered','props_set')
    def __init__(self, kind, selector, raw, props_ordered):
        self.kind          = kind              # comment|at_rule|block|raw
        self.selector      = selector          # normalised selector string or None
        self.raw           = raw               # original text
        self.props_ordered = props_ordered     # list[(prop,val)] in order
        self.props_set     = {p for p,v in props_ordered}

def norm_sel(s: str) -> str:
    return re.sub(r'\s+', ' ', s.strip())

def parse_props(body: str) -> list[tuple[str,str]]:
    props = []
    for m in re.finditer(r'([\w-]+)\s*:\s*([^;}{]+?)\s*;', body, re.DOTALL):
        props.append((m.group(1).strip(), m.group(2).strip()))
    return props

def merge_props(lists: list[list[tuple[str,str]]]) -> list[tuple[str,str]]:
    """Merge multiple prop lists. Last wins; !important beats non-important."""
    merged: dict[str, str] = {}
    # Maintain insertion order of first seen
    order: list[str] = []
    for plist in lists:
        for prop, val in plist:
            if prop not in merged:
                order.append(prop)
                merged[prop] = val
            else:
                existing = merged[prop]
                if '!important' in val:
                    merged[prop] = val
                elif '!important' not in existing:
                    merged[prop] = val
    return [(p, merged[p]) for p in order]

def props_to_str(props: list[tuple[str,str]], indent='    ') -> str:
    return '\n'.join(f'{indent}{p}: {v};' for p,v in props)

# ─── Parse ────────────────────────────────────────────────────────────────────
segments: list[Seg] = []

last_end = 0
for m in SEG_RE.finditer(content):
    gap = content[last_end:m.start()]
    if gap.strip():
        segments.append(Seg('raw', None, gap, []))
    elif gap:
        segments.append(Seg('raw', None, gap, []))

    if m.group(1):
        segments.append(Seg('comment', None, m.group(1), []))
    elif m.group(2):
        segments.append(Seg('at_rule', None, m.group(2), []))
    elif m.group(3):
        segments.append(Seg('at_rule', None, m.group(3), []))
    elif m.group(4):
        raw = m.group(4)
        brace = raw.index('{')
        sel   = norm_sel(raw[:brace])
        body  = raw[brace+1:raw.rindex('}')]
        props = parse_props(body)
        # Skip :root — it has PHP output inside
        if ':root' in sel or not sel:
            segments.append(Seg('at_rule', None, raw, []))
        else:
            segments.append(Seg('block', sel, raw, props))

    last_end = m.end()

tail = content[last_end:]
if tail:
    segments.append(Seg('raw', None, tail, []))

# ─── Find duplicates ──────────────────────────────────────────────────────────
sel_idx: dict[str, list[int]] = defaultdict(list)
for i, seg in enumerate(segments):
    if seg.kind == 'block' and seg.selector:
        sel_idx[seg.selector].append(i)

dups = {sel: idxs for sel, idxs in sel_idx.items() if len(idxs) > 1}
print(f"Found {len(dups)} duplicate selectors to merge")

if DRY_RUN:
    for sel, idxs in sorted(dups.items(), key=lambda x: -len(x[1]))[:30]:
        print(f"  ({len(idxs)}x) {sel}")
    print(f"\n[DRY RUN] Would process {len(dups)} selectors.")
    sys.exit(0)

if not dups:
    print("Nothing to deduplicate. Exiting.")
    sys.exit(0)

# ─── Merge & remove ───────────────────────────────────────────────────────────
removed = 0
merged_count = 0
to_remove: set[int] = set()

for sel, idxs in dups.items():
    all_props = [segments[i].props_ordered for i in idxs]
    merged    = merge_props(all_props)
    # Update last occurrence with merged props
    last_idx  = idxs[-1]
    old_raw   = segments[last_idx].raw
    brace     = old_raw.index('{')
    new_raw   = old_raw[:brace+1] + '\n' + props_to_str(merged) + '\n}'
    # Preserve trailing newlines from original
    trail = old_raw[old_raw.rindex('}')+1:]
    new_raw += trail
    segments[last_idx].raw = new_raw
    # Mark earlier occurrences for removal
    for i in idxs[:-1]:
        to_remove.add(i)
    removed    += len(idxs) - 1
    merged_count += 1

# ─── Write output ─────────────────────────────────────────────────────────────
BAK = CSS_FILE.with_suffix('.css.bak-dedup')
BAK.write_text(content, encoding='utf-8')

result_parts = []
for i, seg in enumerate(segments):
    if i in to_remove:
        continue
    result_parts.append(seg.raw)

result = ''.join(result_parts)

old_lines = content.count('\n')
new_lines = result.count('\n')

CSS_FILE.write_text(result, encoding='utf-8')

print(f"  Merged:  {merged_count} selectors")
print(f"  Removed: {removed} duplicate blocks")
print(f"  Lines:   {old_lines} → {new_lines} (saved {old_lines - new_lines} lines)")
print(f"  Backup:  {BAK}")
print("Done.")
