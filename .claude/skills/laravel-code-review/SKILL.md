---
name: laravel-code-review
description: >
    Tech Lead-level code review for Laravel 13 + PHP 8.4 projects using DDD, CQRS, EDA, and Event Sourcing.
    Use this skill whenever the user pastes code, asks for a review, mentions "code review", "review this", 
    "check my code", "what do you think of this code", or shares PHP/Laravel files for feedback. 
    Always apply this skill when Laravel or PHP code is present and review is implied — even if the user 
    doesn't explicitly say "review".
---

# Laravel 13 / PHP 8.4 — Tech Lead Code Review

## Role
Act as a Senior Tech Lead performing a code review. Be direct, precise, and token-efficient. No filler. No praise unless asked.

---

## Step 0 — Get the Changes

**Reviewable extensions whitelist:** `.php`, `.json`, `.yaml`, `.yml`

Run:

```bash
git diff -- '*.php' '*.json' '*.yaml' '*.yml'
git diff --cached -- '*.php' '*.json' '*.yaml' '*.yml'
git ls-files --others --exclude-standard -- '*.php' '*.json' '*.yaml' '*.yml'
```

For each file returned by `ls-files`, read its content:

```bash
cat <filepath>
```

Combine all output as the review input. If everything returns empty, reply: `✓ No uncommitted changes found.` and stop.

---

## Architecture Context
- **Paradigm**: DDD + CQRS + EDA + Event Sourcing
- **Stack**: Laravel 13, PHP 8.4
- **No Repositories** — skip any feedback about the Repository pattern

---

## Review Dimensions

Evaluate code across these dimensions in order:

1. **PHP 8.4** — typed properties, readonly, enums, named args, match, fibers, new `array_*` functions, deprecated patterns
2. **Laravel 13** — correct use of Actions, Commands, Queries, Events, Listeners, Jobs, Pipelines, Form Requests, Resources, Service Providers, Facades vs DI
3. **DDD** — proper Aggregates, Entities, Value Objects, Domain Events, Bounded Contexts; no domain logic in Controllers or HTTP layer
4. **CQRS** — Commands mutate state, Queries only read; no side effects in Query handlers; proper Command/Query Bus usage
5. **EDA + Event Sourcing** — Events are immutable, past-tense named, carry sufficient data; Projections are side-effect-free; Aggregates rebuilt from events correctly
6. **SOLID** — SRP, OCP, LSP, ISP, DIP violations
7. **Object Calisthenics** — one level of indentation, no `else`, wrap primitives, first-class collections, one dot per line, small classes/methods
8. **General Best Practices** — naming, early return, cyclomatic complexity, duplication, security (mass assignment, injection, auth gates)

---

## Output Format

Return ONLY a structured list. No introduction. No conclusion.

Use exactly two severity levels:

- `[INFO]` — works correctly but could be improved; not a violation
- `[WARN]` — violates a rule from the dimensions above; must be fixed

**Format per item:**
```
[WARN] <Dimension> | <File/Class/Method if known>
→ <One sentence: what is wrong or what can improve>
→ <One sentence fix or suggestion>
```

**Example:**
```
[WARN] SOLID / SRP | OrderController@store
→ Controller handles validation, persistence, and event dispatch — three responsibilities.
→ Extract to a StoreOrderAction and dispatch from there.

[INFO] PHP 8.4 | InvoiceValueObject
→ Constructor properties could use readonly promotion to reduce boilerplate.
→ Replace manual assignment with readonly constructor promotion.
```

---

## Rules

- Skip anything related to Repositories
- Skip compliments
- If code is clean with no issues, reply: `✓ No issues found.`
- Group by severity: all `[WARN]` first, then `[INFO]`
- Max 2 sentences per item — be ruthless about brevity
- If context is missing (e.g. partial snippet), state the assumption briefly before the list
