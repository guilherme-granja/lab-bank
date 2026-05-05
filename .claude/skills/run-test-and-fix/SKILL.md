---
name: run-test-and-fix
description: Run full test suite and fix all errors
---

# Testing and Fixing

## Context Rule
Only read files directly related to the failing test or error.
Do not load unrelated source files into context.
Do not read vendor/ directory.

## Step 1 — Run the test suite
```bash
php artisan test
```

## Step 2 — Analyze and fix errors
If any test fails:
- Read only the files mentioned in the error output
- Identify the root cause (wrong assertion, missing dependency, wrong mock, etc.)
- Apply the fix directly in the source or test file

### Factory rule
If a test requires model creation and no factory exists yet:
- Check `database/factories` for an existing factory
- If it doesn't exist, create it at `database/factories/{ModelName}Factory.php`
- Use the factory in the test via `ModelName::factory()->create()` or `->make()`
- Never instantiate models manually with `new ModelName([...])` in tests

## Step 3 — Re-run once
After applying all fixes, run the suite one more time:
```bash
php artisan test
```

If tests pass → done.
If tests still fail → do NOT run again. Go to Step 4.

## Step 4 — Summarize
Report:
- What was broken and why
- What was fixed (file + change)
- Any remaining issues that could not be resolved
