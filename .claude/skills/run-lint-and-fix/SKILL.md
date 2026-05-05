---
name: run-lint-and-fix
description: Run PHPStan static analysis and Pint code style, fix all errors
---

# Run Lint and Fix Errors

## Context Rule
Only read files directly mentioned in the error output.
Do not load unrelated source files into context.
Do not read vendor/ directory.

## Step 1 — Static Analysis with PHPStan
```bash
vendor/bin/phpstan analyse src
```

If any errors are reported:
- Read only the files mentioned in the error output
- Identify the root cause (type mismatch, undefined method/property, wrong return type, etc.)
- Apply the fix directly in the source file

Re-run once to confirm:
```bash
vendor/bin/phpstan analyse src
```
If errors persist → do NOT run again. Note remaining issues and move to Step 2.

## Step 2 — Code Style with Pint
```bash
vendor/bin/pint src
```

Pint auto-fixes most issues. If it reports files it could not fix:
- Apply the fix manually in the source file

Re-run once to confirm:
```bash
vendor/bin/pint src
```
If issues persist → do NOT run again. Note remaining issues and move to Step 3.

## Step 3 — Summarize
Report:
- What PHPStan flagged and what was fixed (file + change)
- What Pint changed or flagged and what was fixed (file + change)
- Any remaining issues that could not be resolved
