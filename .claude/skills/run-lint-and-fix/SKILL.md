---
name: run-lint-and-fix
description: Run PHPStan static analysis and Pint code style, fix all errors
---

# Run Lint and Fix Errors

## Step 1 — Static Analysis with PHPStan
Execute and capture output:
```bash
vendor/bin/phpstan analyse src
```

If any errors are reported:
- Read the full error output carefully
- Identify the root cause (type mismatch, undefined method/property, wrong return type, etc.)
- Apply the fix directly in the source file
- Re-run PHPStan after fixing to confirm no new errors were introduced
- Repeat fix → re-run until PHPStan reports zero errors

## Step 2 — Code Style with Pint
Execute and capture output:
```bash
vendor/bin/pint src
```

If Pint reports any files changed or errors:
- Review the changes Pint applied automatically
- If Pint could not auto-fix something, fix it manually in the source file
- Re-run Pint after manual fixes to confirm it passes cleanly
- Repeat fix → re-run until Pint reports no changes

## Step 3 — Summarize
Report:
- What PHPStan flagged and what was fixed (file + change)
- What Pint changed or flagged and what was fixed (file + change)
- Any remaining issues if either tool could not be made fully clean
