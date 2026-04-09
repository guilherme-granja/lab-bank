# Code Style

## Design Principles

All code must follow **Clean Code**, **SOLID**, and **Object Calisthenics**. These are non-negotiable — apply them on every class and method written.

### Clean Code
- Names reveal intent: `getUsersWithOverdueInvoices()` not `getData()`.
- Methods do one thing. If you need "and" to describe it, split it.
- No magic numbers or strings — use named constants or enums.
- Guard clauses over nested `if` blocks. Return or throw early.
- No dead code, commented-out code, or `TODO` left in commits.

### SOLID
- **S** - One reason to change per class. Handlers handle, repositories persist, validators validate.
- **O** - Extend via new classes/strategies, not by modifying existing ones.
- **L** - Subtypes must be fully substitutable for their parent type.
- **I** - Small, focused interfaces. No fat contracts that force empty implementations.
- **D** - Depend on abstractions (contracts/interfaces), not on concrete implementations.

### Object Calisthenics
1. Only one level of indentation per method - extract until it hurts.
2. No `else` - use early returns and guard clauses.
3. Wrap primitives in Value Objects when the primitive has domain behaviour (`Money`, `Cpf`, `Email`).
4. First-class collections - wrap arrays in dedicated collection classes when they carry behaviour.
5. One `->` per line (Law of Demeter) - don't reach through objects.
6. No abbreviations - `$customer` not `$cust`, `$invoice` not `$inv`.
7. Keep classes small - aim for under 150 lines; hard limit 200.
8. No more than 2-3 instance variables per class - if you need more, split responsibilities.
9. No anemic getters/setters on domain objects - expose behaviour, not data.

## PSR-12

All PHP code must comply with **PSR-12** (Extended Coding Style). Key rules:

- Files must use `<?php` only (no closing tag), UTF-8 without BOM.
- One blank line after `namespace`, one blank line between `use` blocks and class declaration.
- `use` imports: one per line, alphabetically ordered, grouped (classes / functions / constants) with a blank line between groups.
- Class opening brace on its own line; method opening brace on its own line.
- Visibility (`public`, `protected`, `private`) declared on every property and method.
- No trailing whitespace; files end with a single newline.
- 4-space indentation - never tabs.
- Lines soft-limited to 120 characters.

Laravel Pint (configured for this project) enforces PSR-12 automatically. Always run after any change:

```bash
vendor/bin/pint --dirty --format agent
```
