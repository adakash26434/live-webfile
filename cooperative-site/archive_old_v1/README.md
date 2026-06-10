# archive_old_v1/ — Legacy & Quarantined Files

> **Purpose:** Safe parking lot for files that are **deprecated / replaced / no longer wired** into the active codebase, but should be **kept on disk for one release cycle** in case rollback or reference is needed.

## Rules

1. **Never `require` / `include` / link to any file inside `archive_old_v1/`.**
   The active app must run identically if this folder is deleted.

2. **Before moving a file here, ALWAYS verify zero active references:**
   ```bash
   # from /app
   grep -rln "<filename>" --include="*.php" --include="*.js" --include="*.html" . \
     | grep -v "aakash-coop-cpanel\|archive_old_v1\|/<filename>$"
   ```
   Output must be **empty** (or only the file's own line). If anything references it, do NOT archive — fix the reference first.

3. **Preserve original folder structure inside the archive** so files can be
   restored to their old path by `mv`. Example:
   ```
   archive_old_v1/
   ├── admin/
   │   └── pages-legacy-wrapper.php   # was /app/admin/pages.php (9-line wrapper)
   ├── assets/
   │   ├── css/
   │   │   └── _color-vars.php         # was /app/assets/css/_color-vars.php (deprecated)
   │   └── js/
   │       ├── pwa-init.js
   │       └── v10.6-mobile-helpers.js
   └── README.md
   ```

4. **Keep a `MOVED.log` in the same folder each time you archive:**
   ```
   2026-06-08 | admin/pages.php             | superseded by admin/pages-v2.php → renamed to admin/pages.php
   2026-06-08 | assets/css/_color-vars.php  | merged into assets/css/global-theme.php
   2026-06-08 | assets/js/pwa-init.js       | zero refs found; PWA bootstrap moved to pwa-register.js
   2026-06-08 | assets/js/v10.6-mobile-helpers.js | zero refs; replaced by v9-mobile-fix.js
   ```

5. **Retention policy:** After **one full release cycle** (2-4 weeks of stable
   production), this folder may be deleted entirely — or zipped and moved
   off-server (e.g., cPanel "File Manager → Compress" then download locally
   and remove from server).

## Currently archived

| File | Original Path | Reason | Date |
|------|--------------|--------|------|
| _(none yet — see `MOVED.log` once items are archived)_ |  |  |  |

## Restoration

If a feature breaks and you need to restore a file:
```bash
cd /app
mv archive_old_v1/<original-relative-path> ./<original-relative-path>
```

Then re-test the broken feature. Update `MOVED.log` to note the restoration.
