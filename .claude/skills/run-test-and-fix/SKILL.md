---
name: run-test-and-fix
description: Run full test suite and fix all errors
---

# Run Tests and Fix Errors

## Step 1 — Run the test suite
Execute the following command and capture the output:
```bash
php -dxdebug.mode=coverage ./vendor/bin/pest -c ./phpunit.xml --coverage
```

## Step 2 — Analyze and fix errors
If any test fails:
- Read the full error output carefully
- Identify the root cause (wrong assertion, missing dependency, wrong mock, etc.)
- Apply the fix directly in the source or test file

### Factory rule
If a test requires model creation and no factory exists yet:
- Check `database/factories` for an existing factory
- If it doesn't exist, create it at `database/factories/{ModelName}Factory.php`
- Use the factory in the test via `ModelName::factory()->create()` or `->make()`
- Never instantiate models manually with `new ModelName([...])` in tests

## Step 3 — Re-run to confirm
After fixing, re-run the same command to verify all tests pass.
Repeat fix → re-run until the suite is fully green.

## Step 4 — Summarize
Report:
- What was broken and why
- What was fixed (file + change)
- Any remaining issues if the suite could not be made fully green
